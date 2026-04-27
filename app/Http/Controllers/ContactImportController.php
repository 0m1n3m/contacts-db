<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ContactImportController extends Controller
{
    public function create()
    {
        return view('contacts.import');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $storedPath = $request->file('file')->storeAs(
            'imports',
            'contacts-import-' . now()->format('Ymd-His') . '.' . $request->file('file')->getClientOriginalExtension()
        );

        $fullPath = Storage::path($storedPath);
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Read file
        if (in_array($ext, ['csv', 'txt'], true)) {
            $rows = $this->readCsvRows($fullPath);
        } else {
            $sheets = Excel::toArray([], $fullPath);
            $rows = $sheets[0] ?? [];
        }

        if (count($rows) < 1) {
            return back()->with('status', 'Empty file.');
        }

        // Headers
        $headers = array_map(fn ($h) => trim((string) $h), $rows[0]);
        $headers = array_values(array_filter($headers, fn ($h) => $h !== ''));

        // Strip BOM from first header
        if (isset($headers[0])) {
            $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $headers[0] = trim($headers[0]);
        }

        $columns = $headers;

        // Data rows
        $dataRows = array_slice($rows, 1);

        // Filter completely empty rows
        $dataRows = array_values(array_filter($dataRows, function ($r) {
            if (!is_array($r)) return false;
            foreach ($r as $cell) {
                if (trim((string) $cell) !== '') return true;
            }
            return false;
        }));

        if (count($dataRows) < 1) {
            return back()->with('status', 'No data rows found (only headers).');
        }

        // Preview (first 10)
        $previewRows = [];
        foreach (array_slice($dataRows, 0, 10) as $r) {
            $assoc = [];
            foreach ($headers as $i => $h) {
                $assoc[$h] = $r[$i] ?? null;
            }
            $previewRows[] = $assoc;
        }

        $fields = [
            'contact_category' => 'Contact category *',
            'relationship_status' => 'Relationship status *',
            'use_for_events' => 'Use for events',
            'potential_speaker' => 'Potential speaker',
            'organisation_name' => 'Organisation name',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'job_title' => 'Job title',
            'emails' => 'Emails (list)',
            'phones' => 'Phones (list)',
            'country' => 'Country (ISO code)',
            'organisation_types' => 'Organisation types (list)',
            'keywords' => 'Keywords (list)',
            'relevant_project_programme' => 'Relevant project / programme',
            'expertise_speaking_topics' => 'Expertise / speaking topics',
            'stakeholder_type' => 'Stakeholder type',
            'comment' => 'Comment',
        ];

        $norm = function (string $s): string {
            $s = str_replace(["\u{00A0}", "\t", "\r", "\n"], ' ', $s);
            $s = trim(mb_strtolower($s));
            $s = preg_replace('/\s+/u', ' ', $s);
            $s = str_replace(['/', '\\'], ' ', $s);
            $s = preg_replace('/[()]/', '', $s);
            $s = preg_replace('/[^a-z0-9 ]/iu', '', $s);
            $s = str_replace(' ', '_', $s);
            $s = preg_replace('/_+/', '_', $s);
            return trim($s, '_');
        };

        $originalByNormalized = [];
        foreach ($columns as $c) {
            $originalByNormalized[$norm($c)] = $c;
        }

        $autoMap = [];
        foreach (array_keys($fields) as $field) {
            if (isset($originalByNormalized[$field])) {
                $autoMap[$field] = $originalByNormalized[$field];
            }
        }

        // Minimal aliases
        if (isset($originalByNormalized['organisation'])) {
            $autoMap['organisation_name'] = $originalByNormalized['organisation'];
        }
        if (isset($originalByNormalized['email'])) {
            $autoMap['emails'] = $originalByNormalized['email'];
        }

        return view('contacts.import-map', [
            'storedPath' => $storedPath,
            'columns' => $columns,
            'fields' => $fields,
            'autoMap' => $autoMap,
            'previewRows' => $previewRows,
        ]);
    }

    public function run(Request $request)
    {
        $request->validate([
            'stored_path' => ['required', 'string'],
            'map' => ['required', 'array'],
        ]);

        $storedPath = $request->string('stored_path')->toString();
        $map = $request->input('map', []);

        if (!Storage::exists($storedPath)) {
            return redirect()->route('contacts.import.create')
                ->with('status', 'File not found. Please upload again.');
        }

        $fullPath = Storage::path($storedPath);
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Read file
        if (in_array($ext, ['csv', 'txt'], true)) {
            $rows = $this->readCsvRows($fullPath);
        } else {
            $sheets = Excel::toArray([], $fullPath);
            $rows = $sheets[0] ?? [];
        }

        if (count($rows) < 2) {
            Storage::delete($storedPath);

            return redirect()->route('contacts.import.create')
                ->with('status', 'No data rows found (only headers).');
        }

        // Headers
        $headers = array_map(fn ($h) => trim((string) $h), $rows[0]);
        $headers = array_values(array_filter($headers, fn ($h) => $h !== ''));

        // Strip BOM from first header
        if (isset($headers[0])) {
            $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $headers[0] = trim($headers[0]);
        }

        // Data rows
        $dataRows = array_slice($rows, 1);

        $dataRows = array_values(array_filter($dataRows, function ($r) {
            if (!is_array($r)) return false;
            foreach ($r as $cell) {
                if (trim((string) $cell) !== '') return true;
            }
            return false;
        }));

        if (count($dataRows) < 1) {
            Storage::delete($storedPath);

            return redirect()->route('contacts.import.create')
                ->with('status', 'No data rows found (only headers).');
        }

        // Helper: get value from row by mapping field->column header
        $get = function (array $row, string $field) use ($map, $headers) {
            $colName = $map[$field] ?? null;
            if (!$colName) return null;

            // Strip BOM on colName too (defensive)
            if (is_string($colName)) {
                $colName = preg_replace('/^\x{FEFF}/u', '', $colName);
                $colName = preg_replace('/^\xEF\xBB\xBF/', '', $colName);
                $colName = trim($colName);
            }

            $idx = array_search($colName, $headers, true);
            if ($idx === false) return null;

            $v = $row[$idx] ?? null;
            if (is_string($v)) {
                $v = trim($v);
                if ($v === '') return null;
            }
            return $v;
        };

        $parseList = function ($value): array {
            if ($value === null) return [];
            if (is_array($value)) return array_values(array_filter(array_map('trim', $value)));

            $s = trim((string) $value);
            if ($s === '') return [];

            $delimiter = str_contains($s, ';') ? ';' : (str_contains($s, ',') ? ',' : null);

            $parts = $delimiter ? explode($delimiter, $s) : [$s];
            $parts = array_map(fn ($p) => trim($p), $parts);
            return array_values(array_filter($parts, fn ($p) => $p !== ''));
        };

        $toBool = function ($v) {
            if ($v === null) return null;
            $s = mb_strtolower(trim((string) $v));
            if (in_array($s, ['1', 'true', 'yes', 'y'], true)) return true;
            if (in_array($s, ['0', 'false', 'no', 'n'], true)) return false;
            return null;
        };

        $imported = 0;
        $skippedInvalid = 0;
        $skippedDuplicate = 0;

        foreach ($dataRows as $row) {
            $payload = [
                'contact_category' => $get($row, 'contact_category'),
                'relationship_status' => $get($row, 'relationship_status'),
                'use_for_events' => $get($row, 'use_for_events'),
                'potential_speaker' => $get($row, 'potential_speaker'),
                'organisation_name' => $get($row, 'organisation_name'),
                'first_name' => $get($row, 'first_name'),
                'last_name' => $get($row, 'last_name'),
                'job_title' => $get($row, 'job_title'),
                'country' => $get($row, 'country'),
                'relevant_project_programme' => $get($row, 'relevant_project_programme'),
                'expertise_speaking_topics' => $parseList($get($row, 'expertise_speaking_topics')),
                'stakeholder_type' => $get($row, 'stakeholder_type'),
                'comment' => $get($row, 'comment'),
            ];

            $emails = $parseList($get($row, 'emails'));
            $phones = $parseList($get($row, 'phones'));
            $organisationTypes = $parseList($get($row, 'organisation_types'));
            $keywords = $parseList($get($row, 'keywords'));

            $b = $toBool($payload['use_for_events'] ?? null);
            if ($b !== null) $payload['use_for_events'] = $b;

            $b = $toBool($payload['potential_speaker'] ?? null);
            if ($b !== null) $payload['potential_speaker'] = $b;

            // Minimal required fields
            if (empty($payload['contact_category']) || empty($payload['relationship_status'])) {
                $skippedInvalid++;
                continue;
            }

            // Dedupe:
            // 1) si tiene email(s) válido(s), si cualquiera ya existe en JSON -> skip
            $isDuplicate = false;

            if (count($emails) > 0) {
                foreach ($emails as $email) {
                    $email = trim(mb_strtolower((string) $email));
                    if ($email === '') continue;

                    // Solo deduplicar por email si es un email real
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }

                    $exists = Contact::query()
                        ->whereJsonContains('emails', $email)
                        ->exists();

                    if ($exists) {
                        $isDuplicate = true;
                        break;
                    }
                }
            } else {
                // 2) fallback: organisation + first + last
                $org = mb_strtolower(trim((string) ($payload['organisation_name'] ?? '')));
                $fn  = mb_strtolower(trim((string) ($payload['first_name'] ?? '')));
                $ln  = mb_strtolower(trim((string) ($payload['last_name'] ?? '')));

                if ($org !== '' || $fn !== '' || $ln !== '') {
                    $exists = Contact::query()
                        ->whereRaw('lower(coalesce(organisation_name, \'\')) = ?', [$org])
                        ->whereRaw('lower(coalesce(first_name, \'\')) = ?', [$fn])
                        ->whereRaw('lower(coalesce(last_name, \'\')) = ?', [$ln])
                        ->exists();

                    if ($exists) $isDuplicate = true;
                }
            }

            if ($isDuplicate) {
                $skippedDuplicate++;
                continue;
            }

            $payload['emails'] = $emails;
            $payload['phones'] = $phones;
            $payload['organisation_types'] = $organisationTypes;
            $payload['keywords'] = $keywords;

            try {
                Contact::create($payload);
                $imported++;
            } catch (\Throwable $e) {
                report($e);
                $skippedInvalid++;
                continue;
            }
        }

        Storage::delete($storedPath);

        return redirect()->route('contacts.index')->with(
            'status',
            "Import finished. Imported: {$imported}. Skipped invalid: {$skippedInvalid}. Skipped duplicates: {$skippedDuplicate}."
        );
    }

    private function readCsvRows(string $fullPath): array
    {
        $handle = fopen($fullPath, 'rb');
        if ($handle === false) {
            return [];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [];
        }

        // detect delimiter among TAB, comma, semicolon (pick the one with most occurrences)
        $candidates = ["\t", ",", ";"];
        $delimiter = ",";
        $bestCount = -1;

        foreach ($candidates as $d) {
            $count = substr_count($firstLine, $d);
            if ($count > $bestCount) {
                $bestCount = $count;
                $delimiter = $d;
            }
        }

        rewind($handle);

        $rows = [];

        $cleanCell = function ($v) {
            if ($v === null) return null;
            $s = (string) $v;

            // strip BOM if present
            $s = preg_replace('/^\x{FEFF}/u', '', $s);
            $s = preg_replace('/^\xEF\xBB\xBF/', '', $s);

            $s = trim($s);

            // Convert doubled quotes "" -> "
            $s = str_replace('""', '"', $s);

            // If the whole cell is wrapped in quotes, unwrap
            if (strlen($s) >= 2 && $s[0] === '"' && substr($s, -1) === '"') {
                $s = substr($s, 1, -1);
            }

            // If only a leading quote is hanging, remove it
            if (strlen($s) >= 1 && $s[0] === '"') {
                $s = ltrim($s, '"');
            }

            // If only a trailing quote is hanging, remove it
            if (strlen($s) >= 1 && substr($s, -1) === '"') {
                $s = rtrim($s, '"');
            }

            $s = trim($s);

            return $s === '' ? null : $s;
        };

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) break;

            $line = rtrim($line, "\r\n");
            if (trim($line) === '') continue;

            // parse line with detected delimiter
            $data = str_getcsv($line, $delimiter, '"', "\\");

            // If it still didn't split but delimiter exists, last resort split
            if (is_array($data) && count($data) === 1 && str_contains($line, $delimiter)) {
                $data = explode($delimiter, $line);
            }

            // clean every cell
            $data = array_map($cleanCell, $data);

            $rows[] = $data;
        }

        fclose($handle);

        return $rows;
    }
}
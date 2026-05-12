<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        // whitelist: columnas ordenables (no JSON)
        $allowedSorts = [
            'contact_category',
            'relationship_status',
            'use_for_events',
            'potential_speaker',
            'organisation_name',
            'first_name',
            'last_name',
            'job_title',
            'country',
            'relevant_project_programme',
            'stakeholder_type',
            'created_at',
            'updated_at',
        ];

        $sort = $request->query('sort', 'updated_at');
        $dir  = $request->query('dir', 'desc');

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'updated_at';
        }

        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $query = Contact::query();

        // --------------------
        // Filters (q + columns)
        // --------------------
        $q = trim((string) $request->query('q', ''));

        // Column filters (text "contains")
        $filters = [
            'first_name' => trim((string) $request->query('first_name', '')),
            'last_name' => trim((string) $request->query('last_name', '')),
            'organisation_name' => trim((string) $request->query('organisation_name', '')),
            'stakeholder_type' => trim((string) $request->query('stakeholder_type', '')),
            'comment' => trim((string) $request->query('comment', '')),
            'relevant_project_programme' => trim((string) $request->query('relevant_project_programme', '')),
            'job_title' => trim((string) $request->query('job_title', '')),
            'contact_category' => trim((string) $request->query('contact_category', '')),
            'relationship_status' => trim((string) $request->query('relationship_status', '')),
        ];

        foreach ($filters as $col => $val) {
            if ($val !== '') {
                $query->where($col, 'like', '%' . $val . '%');
            }
        }

        $countryFilter = trim((string) $request->query('country', ''));
        if ($countryFilter !== '') {
            // If user typed an ISO code (2 letters), match exactly
            if (preg_match('/^[a-z]{2}$/i', $countryFilter)) {
                $query->where('country', strtoupper($countryFilter));
            } else {
                // Otherwise, try matching by country name from config('countries')
                $countries = (array) config('countries', []);
                $needle = mb_strtolower($countryFilter);

                $matchedCodes = [];
                foreach ($countries as $code => $name) {
                    $name = (string) $name;
                    if ($name !== '' && str_contains(mb_strtolower($name), $needle)) {
                        $matchedCodes[] = strtoupper((string) $code);
                    }
                }

                if (count($matchedCodes) > 0) {
                    $query->whereIn('country', $matchedCodes);
                } else {
                    // fallback to previous behavior in case DB stores non-standard values
                    $query->where('country', 'like', '%' . $countryFilter . '%');
                }
            }
        }

        // JSON array columns: contains item
        $jsonFilters = [
            'emails' => trim((string) $request->query('emails', '')),
            'phones' => trim((string) $request->query('phones', '')),
            'keywords' => trim((string) $request->query('keywords', '')),
            'organisation_types' => trim((string) $request->query('organisation_types', '')),
            'expertise_speaking_topics' => trim((string) $request->query('expertise_speaking_topics', '')),
        ];

        foreach ($jsonFilters as $col => $val) {
            if ($val !== '') {
                // match JSON string element to reduce false positives
                $needle = '%"' . str_replace('"', '', $val) . '"%';
                $query->where($col, 'like', $needle);
            }
        }

        // Global search (applies on top of column filters)
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $like = '%' . $q . '%';

                // normal columns
                $sub->orWhere('organisation_name', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('job_title', 'like', $like)
                    ->orWhere('stakeholder_type', 'like', $like)
                    ->orWhere('comment', 'like', $like)
                    ->orWhere('relevant_project_programme', 'like', $like)
                    ->orWhere('contact_category', 'like', $like)
                    ->orWhere('relationship_status', 'like', $like)
                    ->orWhere('country', 'like', $like);

                // extra: match by country NAME -> filter by ISO code stored in DB
                $countries = (array) config('countries', []);
                $needle = mb_strtolower(trim($q));
                $matchedCodes = [];

                if ($needle !== '') {
                    foreach ($countries as $code => $name) {
                        $name = (string) $name;
                        if ($name !== '' && str_contains(mb_strtolower($name), $needle)) {
                            $matchedCodes[] = strtoupper((string) $code);
                        }
                    }
                }

                if (count($matchedCodes) > 0) {
                    $sub->orWhereIn('country', $matchedCodes);
                }

                // json columns (search as text, cross-db)
                $jsonLike = '%"' . str_replace('"', '', $q) . '"%';
                $sub->orWhere('emails', 'like', $jsonLike)
                    ->orWhere('phones', 'like', $jsonLike)
                    ->orWhere('keywords', 'like', $jsonLike)
                    ->orWhere('organisation_types', 'like', $jsonLike)
                    ->orWhere('expertise_speaking_topics', 'like', $jsonLike);
            });
        }

        // --------------------
        // Sorting
        // --------------------
        if (in_array($sort, ['first_name', 'last_name', 'organisation_name', 'job_title', 'stakeholder_type', 'country', 'contact_category', 'relationship_status', 'relevant_project_programme'], true)) {
            $query->orderByRaw("TRIM(LOWER($sort)) $dir");
        } else {
            $query->orderBy($sort, $dir);
        }

        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $contacts = $query
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('contacts.index', compact('contacts', 'sort', 'dir', 'allowedSorts'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_category' => ['required', 'string', 'max:255'],
            'relationship_status' => ['required', 'string', 'max:255'],

            'use_for_events' => ['nullable', 'boolean'],
            'potential_speaker' => ['nullable', 'boolean'],

            'organisation_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],

            // Por ahora como texto (una por línea). Luego lo convertimos a JSON array.
            'emails_text' => ['nullable', 'string'],
            'phones_text' => ['nullable', 'string'],

            'country' => ['nullable', 'string', 'max:255'],

            'organisation_types_text' => ['nullable', 'string'],
            'keywords_text' => ['nullable', 'string'],

            'relevant_project_programme' => ['nullable', 'string', 'max:255'],
            'expertise_speaking_topics' => ['nullable', 'string'],

            'stakeholder_type' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ]);

        $toLines = fn (?string $v) => collect(preg_split("/\r\n|\n|\r/", trim((string) $v)))
            ->map(fn ($x) => trim($x))
            ->filter()
            ->values()
            ->all();

        $contact = new \App\Models\Contact();
        $contact->contact_category = $data['contact_category'];
        $contact->relationship_status = $data['relationship_status'];
        $contact->use_for_events = (bool)($data['use_for_events'] ?? false);
        $contact->potential_speaker = (bool)($data['potential_speaker'] ?? false);

        $contact->organisation_name = $data['organisation_name'] ?? null;
        $contact->first_name = $data['first_name'] ?? null;
        $contact->last_name = $data['last_name'] ?? null;
        $contact->job_title = $data['job_title'] ?? null;

        $contact->emails = $toLines($data['emails_text'] ?? null);
        $contact->phones = $toLines($data['phones_text'] ?? null);

        $contact->country = $data['country'] ?? null;

        $contact->organisation_types = $toLines($data['organisation_types_text'] ?? null);
        $contact->keywords = $toLines($data['keywords_text'] ?? null);

        $contact->relevant_project_programme = $data['relevant_project_programme'] ?? null;
        $contact->expertise_speaking_topics = $toLines($data['expertise_speaking_topics'] ?? null);

        $contact->stakeholder_type = $data['stakeholder_type'] ?? null;
        $contact->comment = $data['comment'] ?? null;

        $contact->save();

        return redirect()->route('contacts.index');
    }

    public function show(Contact $contact)
    {
        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'contact_category' => ['required', 'string', 'max:255'],
            'relationship_status' => ['required', 'string', 'max:255'],

            'use_for_events' => ['nullable', 'boolean'],
            'potential_speaker' => ['nullable', 'boolean'],

            'organisation_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],

            'emails_text' => ['nullable', 'string'],
            'phones_text' => ['nullable', 'string'],

            'country' => ['nullable', 'string', 'max:255'],

            'organisation_types_text' => ['nullable', 'string'],
            'keywords_text' => ['nullable', 'string'],

            'relevant_project_programme' => ['nullable', 'string', 'max:255'],
            'expertise_speaking_topics' => ['nullable', 'string'],

            'stakeholder_type' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ]);

        $toLines = fn (?string $v) => collect(preg_split("/\r\n|\n|\r/", trim((string) $v)))
            ->map(fn ($x) => trim($x))
            ->filter()
            ->values()
            ->all();

        $contact->contact_category = $data['contact_category'];
        $contact->relationship_status = $data['relationship_status'];
        $contact->use_for_events = (bool)($data['use_for_events'] ?? false);
        $contact->potential_speaker = (bool)($data['potential_speaker'] ?? false);

        $contact->organisation_name = $data['organisation_name'] ?? null;
        $contact->first_name = $data['first_name'] ?? null;
        $contact->last_name = $data['last_name'] ?? null;
        $contact->job_title = $data['job_title'] ?? null;

        $contact->emails = $toLines($data['emails_text'] ?? null);
        $contact->phones = $toLines($data['phones_text'] ?? null);

        $contact->country = $data['country'] ?? null;

        $contact->organisation_types = $toLines($data['organisation_types_text'] ?? null);
        $contact->keywords = $toLines($data['keywords_text'] ?? null);

        $contact->relevant_project_programme = $data['relevant_project_programme'] ?? null;
        $contact->expertise_speaking_topics = $toLines($data['expertise_speaking_topics'] ?? null);

        $contact->stakeholder_type = $data['stakeholder_type'] ?? null;
        $contact->comment = $data['comment'] ?? null;

        $contact->save();

        return redirect()->route('contacts.show', $contact);
    }

    public function destroy(Contact $contact)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('status', 'Contact deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:contacts,id'],
        ]);

        \DB::transaction(function () use ($data) {
            Contact::whereIn('id', $data['ids'])->delete();
        });

        return redirect()->route('contacts.index')
            ->with('status', 'Deleted ' . count($data['ids']) . ' contact(s).');
    }

    public function export(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:contacts,id'],
        ]);

        $ids = $data['ids'];

        $columns = [
            'id',
            'contact_category',
            'relationship_status',
            'use_for_events',
            'potential_speaker',
            'organisation_name',
            'first_name',
            'last_name',
            'job_title',
            'emails',
            'phones',
            'country',
            'organisation_types',
            'keywords',
            'relevant_project_programme',
            'expertise_speaking_topics',
            'stakeholder_type',
            'comment',
            'created_at',
            'updated_at',
        ];

        $listCols = ['emails', 'phones', 'organisation_types', 'keywords', 'expertise_speaking_topics'];

        $filename = 'contacts-selected-' . now()->format('Ymd-His') . '.csv';

        $cleanScalar = function ($v) {
            if ($v === null) return null;
            $s = is_string($v) ? $v : (string) $v;

            // keep CSV single-line per record
            $s = str_replace(["\r\n", "\n", "\r", "\t"], ' ', $s);
            $s = preg_replace('/\s+/', ' ', $s);
            $s = trim($s);

            return $s === '' ? null : $s;
        };

        $cleanList = function ($v) {
            if ($v === null) return [];

            if (is_string($v)) {
                $decoded = json_decode($v, true);
                if (is_array($decoded)) $v = $decoded;
            }

            if (!is_array($v)) $v = [$v];

            $items = array_values(array_filter(array_map(function ($x) {
                $s = trim((string) $x);
                $s = str_replace(["\r\n", "\n", "\r", "\t"], ' ', $s);
                $s = preg_replace('/\s+/', ' ', $s);
                $s = trim($s);
                return $s === '' ? null : $s;
            }, $v)));

            return $items;
        };

        $callback = function () use ($ids, $columns, $listCols, $cleanScalar, $cleanList) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens it correctly
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $columns);

            Contact::query()
                ->select($columns)
                ->whereIn('id', $ids)
                ->orderByDesc('id')
                ->chunk(500, function ($rows) use ($out, $columns, $listCols, $cleanScalar, $cleanList) {
                    foreach ($rows as $row) {
                        $line = [];

                        foreach ($columns as $col) {
                            $v = $row->{$col};

                            if (in_array($col, $listCols, true)) {
                                $arr = $cleanList($v);
                                $v = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            } else {
                                $v = $cleanScalar($v);
                            }

                            if (in_array($col, ['use_for_events', 'potential_speaker'], true)) {
                                $v = $v ? 1 : 0;
                            }

                            $line[] = $v;
                        }

                        fputcsv($out, $line);
                    }
                });

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

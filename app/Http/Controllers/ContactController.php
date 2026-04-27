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

        if (in_array($sort, ['first_name', 'last_name', 'organisation_name', 'job_title', 'stakeholder_type', 'country', 'contact_category', 'relationship_status', 'relevant_project_programme'], true)) {
            // Orden “normalizado” para texto
            $query->orderByRaw("TRIM(LOWER($sort)) $dir");
        } else {
            $query->orderBy($sort, $dir);
        }

        $contacts = $query
            ->orderBy('id', 'desc')
            ->paginate(20)
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
        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('status', 'Contact deleted.');
    }
}

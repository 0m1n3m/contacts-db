{{-- resources/views/contacts/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Contacts
        </h2>
    </x-slot>

    @php
        // helper para construir links de sort conservando querystring
        $sortLink = function (string $col) use ($sort, $dir) {
            $newDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $newDir, 'page' => 1]);
        };

        $sortIcon = function (string $col) use ($sort, $dir) {
            if ($sort !== $col) return '↕';
            return $dir === 'asc' ? '↑' : '↓';
        };

        $countryName = function ($code) {
            if (!$code) return null;
            return config('countries')[$code] ?? $code;
        };

        $textOrDash = function ($value) {
            if ($value === null) return '—';
            $s = trim((string) $value);
            return $s === '' ? '—' : $s;
        };

        $listOrDash = function ($value) {
            if (empty($value)) {
                return '—';
            }

            if (is_string($value)) {
                $value = trim($value);
                return $value === '' ? '—' : e($value);
            }

            if (!is_array($value)) {
                return e((string) $value);
            }

            $items = array_values(array_filter(array_map(function ($v) {
                $v = trim((string) $v);
                return $v === '' ? null : $v;
            }, $value)));

            if (count($items) === 0) return '—';

            $html = '<ul class="list-disc pl-4 space-y-0.5">';
            foreach ($items as $item) {
                $html .= '<li>' . e($item) . '</li>';
            }
            $html .= '</ul>';

            return $html;
        };

        $boolLabel = function ($v) {
            if ($v === null) return '—';
            return $v ? 'Yes' : 'No';
        };
    @endphp

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('status'))
                        <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div class="text-sm">
                            <strong>Total:</strong> {{ $contacts->total() }}
                        </div>

                        <div class="flex flex-wrap gap-4 text-sm">
                            @if (auth()->user()?->role && in_array(auth()->user()->role, ['admin','editor'], true))
                                <a class="underline" href="{{ route('contacts.create') }}">Create contact</a>
                                <a class="underline" href="{{ route('contacts.import.create') }}">Import contacts</a>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-auto border rounded">
                        <table class="min-w-[1600px] w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    {{-- Name (computed) --}}
                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('first_name') }}">
                                            First name {!! $sortIcon('first_name') !!}
                                        </a>
                                    </th>
                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('last_name') }}">
                                            Last name {!! $sortIcon('last_name') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('organisation_name') }}">
                                            Organisation {!! $sortIcon('organisation_name') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('contact_category') }}">
                                            Contact category {!! $sortIcon('contact_category') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('relationship_status') }}">
                                            Relationship status {!! $sortIcon('relationship_status') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('use_for_events') }}">
                                            Use for events {!! $sortIcon('use_for_events') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('potential_speaker') }}">
                                            Potential speaker {!! $sortIcon('potential_speaker') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('job_title') }}">
                                            Job title {!! $sortIcon('job_title') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        Emails
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        Phones
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('country') }}">
                                            Country {!! $sortIcon('country') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        Organisation types
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        Keywords
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('relevant_project_programme') }}">
                                            Relevant project / programme {!! $sortIcon('relevant_project_programme') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        Expertise / speaking topics
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('stakeholder_type') }}">
                                            Stakeholder type {!! $sortIcon('stakeholder_type') !!}
                                        </a>
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        Comment
                                    </th>

                                    <th class="text-left px-3 py-2 border-b whitespace-nowrap">
                                        <a class="underline" href="{{ $sortLink('updated_at') }}">
                                            Updated {!! $sortIcon('updated_at') !!}
                                        </a>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($contacts as $contact)
                                    <tr class="odd:bg-white even:bg-gray-50 hover:bg-yellow-50">
                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap">
                                            <a class="underline" href="{{ route('contacts.show', $contact) }}">
                                                {{ $contact->first_name ?? '—' }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap">
                                            {{ $contact->last_name ?? '—' }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {{ $contact->organisation_name ?? '—' }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap">
                                            {{ $contact->contact_category }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap">
                                            {{ $contact->relationship_status }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap">
                                            {{ $boolLabel($contact->use_for_events) }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap">
                                            {{ $boolLabel($contact->potential_speaker) }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {{ $contact->job_title ?? '—' }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {!! $listOrDash($contact->emails) !!}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {!! $listOrDash($contact->phones) !!}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap">
                                            {{ $countryName($contact->country) ?? '—' }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {!! $listOrDash($contact->organisation_types) !!}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {!! $listOrDash($contact->keywords) !!}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {{ $contact->relevant_project_programme ?? '—' }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {!! $listOrDash($contact->expertise_speaking_topics) !!}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap">
                                            {{ $contact->stakeholder_type ?? '—' }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top">
                                            {{ $contact->comment ?? '—' }}
                                        </td>

                                        <td class="px-3 py-2 border-b align-top whitespace-nowrap text-gray-600">
                                            {{ optional($contact->updated_at)->format('Y-m-d H:i') ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="18" class="px-3 py-6 text-center text-gray-600">
                                            No contacts yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $contacts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
{{-- resources/views/contacts/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Contacts
        </h2>
    </x-slot>

    @php
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

        $boolLabel = function ($v) {
            if ($v === null) return '—';
            return $v ? 'Yes' : 'No';
        };

        $compact = (int) request()->query('compact', 0) === 1;

        // Text rendering
        $textOrDash = function ($value) {
            if ($value === null) return '—';
            $s = trim((string) $value);
            return $s === '' ? '—' : $s;
        };

        // Render as UL/LI (emails/phones)
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
                $html .= '<li class="whitespace-nowrap">' . e($item) . '</li>';
            }
            $html .= '</ul>';

            return $html;
        };

        // Render as chips (keywords/orgTypes/expertise)
        $chipsOrDash = function ($value) {
            if (empty($value)) return '—';

            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') return '—';
                $value = [$value];
            }

            if (!is_array($value)) {
                $value = [ (string) $value ];
            }

            $items = array_values(array_filter(array_map(function ($v) {
                $v = trim((string) $v);
                return $v === '' ? null : $v;
            }, $value)));

            if (count($items) === 0) return '—';

            $html = '<div class="flex flex-wrap gap-1.5">';
            foreach ($items as $item) {
                $html .= '<span class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2 py-0.5 text-[11px] text-gray-800">'
                      . e($item) .
                      '</span>';
            }
            $html .= '</div>';

            return $html;
        };
    @endphp

    <div class="py-6"
        x-data="contactsIndexTable({{ $contacts->pluck('id')->values()->toJson() }})"
        x-init="init()">
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

                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            {{-- Compact toggle (querystring) --}}
                            <a class="underline"
                               href="{{ request()->fullUrlWithQuery(['compact' => $compact ? 0 : 1, 'page' => 1]) }}">
                                {{ $compact ? 'Normal view' : 'Compact view' }}
                            </a>

                            {{-- Pagination picker (querystring) --}}
                            <form method="GET" action="{{ url()->current() }}" class="inline-flex items-center gap-2">
                                {{-- conserva el resto de query params --}}
                                @foreach (request()->except(['per_page','page']) as $k => $v)
                                    @if (is_array($v))
                                        @foreach ($v as $vv)
                                            <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endif
                                @endforeach

                                <label class="text-sm text-gray-700">Per page</label>
                                <select name="per_page" class="border rounded px-2 py-1 text-sm bg-[right_0px_center]" onchange="this.form.submit()">
                                    @foreach ([10,25,50,100] as $n)
                                        <option value="{{ $n }}" @selected((int)request('per_page',25) === $n)>{{ $n }}</option>
                                    @endforeach
                                </select>
                            </form>

                            {{-- Column picker --}}
                            <button type="button" class="underline" @click="columnsOpen = !columnsOpen">
                                Columns
                            </button>

                            <button type="button" class="underline text-gray-700" @click="resetColumns()">
                                Reset columns
                            </button>

                            @if (auth()->user()?->role === 'admin')
                                <button type="button"
                                        class="underline text-red-700"
                                        :class="selectedIds.length === 0 ? 'opacity-40 cursor-not-allowed' : ''"
                                        @click="if (selectedIds.length > 0) bulkOpen = true">
                                    Delete selected (<span x-text="selectedIds.length"></span>)
                                </button>
                            @endif

                            @if (auth()->user()?->role && in_array(auth()->user()->role, ['admin','editor'], true))
                                <a class="underline" href="{{ route('contacts.create') }}">Create contact</a>
                                <a class="underline" href="{{ route('contacts.import.create') }}">Import contacts</a>
                            @endif
                        </div>
                    </div>

                    {{-- Columns panel --}}
                    <div x-show="columnsOpen"
                         x-transition
                         class="mb-4 rounded border bg-gray-50 p-3 text-sm">
                        <div class="font-semibold mb-2">Show / hide columns</div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <template x-for="c in columnList" :key="c.key">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox"
                                           class="rounded"
                                           :checked="isVisible(c.key)"
                                           @change="toggleColumn(c.key)">
                                    <span x-text="c.label"></span>
                                </label>
                            </template>
                        </div>

                        <div class="mt-3 text-xs text-gray-600">
                            Hidden columns are remembered in this browser (localStorage).
                        </div>
                    </div>

                    @if (auth()->user()?->role === 'admin')
                        <form id="bulkDeleteForm" method="POST" action="{{ route('contacts.bulk-destroy') }}">
                            @csrf
                            <template x-for="id in selectedIds" :key="id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                        </form>

                        <!-- Bulk delete modal -->
                        <div x-show="bulkOpen" x-transition.opacity
                            class="fixed inset-0 bg-black/50 z-50"
                            @click="bulkOpen = false"
                            aria-hidden="true">
                        </div>

                        <div x-show="bulkOpen" x-transition
                            class="fixed inset-0 z-50 flex items-center justify-center p-4"
                            aria-modal="true"
                            role="dialog">
                            <div class="w-full max-w-md rounded bg-white shadow-lg p-6"
                                @click.stop>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Delete selected contacts?
                                </h3>

                                <p class="mt-2 text-sm text-gray-600">
                                    You are about to delete <strong><span x-text="selectedIds.length"></span></strong> contact(s).
                                    This action cannot be undone.
                                </p>

                                <div class="mt-6 flex justify-end gap-3">
                                    <button type="button"
                                            class="px-4 py-2 border rounded"
                                            @click="bulkOpen = false">
                                        Cancel
                                    </button>

                                    <button type="button"
                                            class="px-4 py-2 rounded bg-red-700 text-white"
                                            @click="
                                                bulkOpen = false;
                                                document.getElementById('bulkDeleteForm').submit();
                                            ">
                                        Yes, delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- IMPORTANT: sticky needs a scroll container --}}
                    <div class="overflow-auto border rounded max-h-[70vh]">
                        <table class="min-w-[1700px] w-full {{ $compact ? 'text-xs' : 'text-sm' }}">
                            <thead class="bg-gray-50 sticky top-0 z-20">
                                <tr>
                                    {{-- Admin only delete contact checkbox --}}
                                    @if (auth()->user()?->role === 'admin')
                                        <th class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50 w-[48px] min-w-[48px]">
                                            <input type="checkbox"
                                                x-ref="selectAll"
                                                class="rounded"
                                                :checked="allPageSelected()"
                                                @change="toggleAllPage($event.target.checked)">
                                        </th>
                                    @endif

                                    {{-- First name --}}
                                    <th x-show="isVisible('first_name')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('first_name') }}">
                                            First name {!! $sortIcon('first_name') !!}
                                        </a>
                                    </th>

                                    {{-- Last name --}}
                                    <th x-show="isVisible('last_name')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('last_name') }}">
                                            Last name {!! $sortIcon('last_name') !!}
                                        </a>
                                    </th>

                                    {{-- Organisation --}}
                                    <th x-show="isVisible('organisation_name')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('organisation_name') }}">
                                            Organisation {!! $sortIcon('organisation_name') !!}
                                        </a>
                                    </th>

                                    {{-- Contact category --}}
                                    <th x-show="isVisible('contact_category')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('contact_category') }}">
                                            Contact category {!! $sortIcon('contact_category') !!}
                                        </a>
                                    </th>

                                    {{-- Relationship status --}}
                                    <th x-show="isVisible('relationship_status')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('relationship_status') }}">
                                            Relationship status {!! $sortIcon('relationship_status') !!}
                                        </a>
                                    </th>

                                    {{-- Use for events --}}
                                    <th x-show="isVisible('use_for_events')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('use_for_events') }}">
                                            Use for events {!! $sortIcon('use_for_events') !!}
                                        </a>
                                    </th>

                                    {{-- Potential speaker --}}
                                    <th x-show="isVisible('potential_speaker')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('potential_speaker') }}">
                                            Potential speaker {!! $sortIcon('potential_speaker') !!}
                                        </a>
                                    </th>

                                    {{-- Job title --}}
                                    <th x-show="isVisible('job_title')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('job_title') }}">
                                            Job title {!! $sortIcon('job_title') !!}
                                        </a>
                                    </th>

                                    {{-- Emails --}}
                                    <th x-show="isVisible('emails')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        Emails
                                    </th>

                                    {{-- Phones --}}
                                    <th x-show="isVisible('phones')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50 w-[280px] min-w-[280px]">
                                        Phones
                                    </th>

                                    {{-- Country --}}
                                    <th x-show="isVisible('country')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('country') }}">
                                            Country {!! $sortIcon('country') !!}
                                        </a>
                                    </th>

                                    {{-- Organisation types --}}
                                    <th x-show="isVisible('organisation_types')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        Organisation types
                                    </th>

                                    {{-- Keywords --}}
                                    <th x-show="isVisible('keywords')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        Keywords
                                    </th>

                                    {{-- Relevant project/programme --}}
                                    <th x-show="isVisible('relevant_project_programme')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('relevant_project_programme') }}">
                                            Relevant project / programme {!! $sortIcon('relevant_project_programme') !!}
                                        </a>
                                    </th>

                                    {{-- Expertise --}}
                                    <th x-show="isVisible('expertise_speaking_topics')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        Expertise / speaking topics
                                    </th>

                                    {{-- Stakeholder type --}}
                                    <th x-show="isVisible('stakeholder_type')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('stakeholder_type') }}">
                                            Stakeholder type {!! $sortIcon('stakeholder_type') !!}
                                        </a>
                                    </th>

                                    {{-- Comment --}}
                                    <th x-show="isVisible('comment')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        Comment
                                    </th>

                                    {{-- Updated --}}
                                    <th x-show="isVisible('updated_at')"
                                        class="text-left px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b whitespace-nowrap bg-gray-50">
                                        <a class="underline" href="{{ $sortLink('updated_at') }}">
                                            Updated {!! $sortIcon('updated_at') !!}
                                        </a>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($contacts as $contact)
                                    <tr class="odd:bg-white even:bg-gray-50 hover:bg-yellow-50">
                                        @if (auth()->user()?->role === 'admin')
                                            <td class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top w-[48px] min-w-[48px]">
                                                <input type="checkbox"
                                                    class="rounded"
                                                    :checked="isSelected({{ $contact->id }})"
                                                    @change="toggleRow({{ $contact->id }})">
                                            </td>
                                        @endif

                                        <td x-show="isVisible('first_name')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap">
                                            <a class="underline" href="{{ route('contacts.show', $contact) }}">
                                                {{ $contact->first_name ?? '—' }}
                                            </a>
                                        </td>

                                        <td x-show="isVisible('last_name')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap">
                                            {{ $contact->last_name ?? '—' }}
                                        </td>

                                        <td x-show="isVisible('organisation_name')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top">
                                            {{ $contact->organisation_name ?? '—' }}
                                        </td>

                                        <td x-show="isVisible('contact_category')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap">
                                            {{ $textOrDash($contact->contact_category) }}
                                        </td>

                                        <td x-show="isVisible('relationship_status')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap">
                                            {{ $textOrDash($contact->relationship_status) }}
                                        </td>

                                        <td x-show="isVisible('use_for_events')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap">
                                            {{ $boolLabel($contact->use_for_events) }}
                                        </td>

                                        <td x-show="isVisible('potential_speaker')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap">
                                            {{ $boolLabel($contact->potential_speaker) }}
                                        </td>

                                        <td x-show="isVisible('job_title')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top">
                                            {{ $contact->job_title ?? '—' }}
                                        </td>

                                        <td x-show="isVisible('emails')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top">
                                            {!! $listOrDash($contact->emails) !!}
                                        </td>

                                        <td x-show="isVisible('phones')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top w-[280px] min-w-[280px]">
                                            {!! $listOrDash($contact->phones) !!}
                                        </td>

                                        <td x-show="isVisible('country')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap">
                                            {{ $countryName($contact->country) ?? '—' }}
                                        </td>

                                        <td x-show="isVisible('organisation_types')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top">
                                            {!! $chipsOrDash($contact->organisation_types) !!}
                                        </td>

                                        <td x-show="isVisible('keywords')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top">
                                            {!! $chipsOrDash($contact->keywords) !!}
                                        </td>

                                        <td x-show="isVisible('relevant_project_programme')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top">
                                            {{ $contact->relevant_project_programme ?? '—' }}
                                        </td>

                                        <td x-show="isVisible('expertise_speaking_topics')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top">
                                            {!! $chipsOrDash($contact->expertise_speaking_topics) !!}
                                        </td>

                                        <td x-show="isVisible('stakeholder_type')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap">
                                            {{ $contact->stakeholder_type ?? '—' }}
                                        </td>

                                        <td x-show="isVisible('comment')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top">
                                            {{ $contact->comment ?? '—' }}
                                        </td>

                                        <td x-show="isVisible('updated_at')" class="px-3 {{ $compact ? 'py-1' : 'py-2' }} border-b align-top whitespace-nowrap text-gray-600">
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

                    {{-- Alpine component --}}
                    <script>
                        function contactsIndexTable(pageIds) {
                            const STORAGE_KEY = 'contacts.index.columns.v1';

                            const defaultVisible = {
                                first_name: true,
                                last_name: true,
                                organisation_name: true,
                                contact_category: true,
                                relationship_status: true,
                                use_for_events: true,
                                potential_speaker: true,
                                job_title: true,
                                emails: true,
                                phones: true,
                                country: true,
                                organisation_types: true,
                                keywords: true,
                                relevant_project_programme: true,
                                expertise_speaking_topics: true,
                                stakeholder_type: true,
                                comment: false,
                                updated_at: true,
                            };

                            return {
                                // panel columns
                                columnsOpen: false,
                                bulkOpen: false,
                                visible: { ...defaultVisible },

                                // bulk select (page only)
                                pageIds: Array.isArray(pageIds) ? pageIds : [],
                                selectedIds: [],

                                columnList: [
                                    { key: 'first_name', label: 'First name' },
                                    { key: 'last_name', label: 'Last name' },
                                    { key: 'organisation_name', label: 'Organisation' },
                                    { key: 'contact_category', label: 'Contact category' },
                                    { key: 'relationship_status', label: 'Relationship status' },
                                    { key: 'use_for_events', label: 'Use for events' },
                                    { key: 'potential_speaker', label: 'Potential speaker' },
                                    { key: 'job_title', label: 'Job title' },
                                    { key: 'emails', label: 'Emails' },
                                    { key: 'phones', label: 'Phones' },
                                    { key: 'country', label: 'Country' },
                                    { key: 'organisation_types', label: 'Organisation types' },
                                    { key: 'keywords', label: 'Keywords' },
                                    { key: 'relevant_project_programme', label: 'Relevant project / programme' },
                                    { key: 'expertise_speaking_topics', label: 'Expertise / speaking topics' },
                                    { key: 'stakeholder_type', label: 'Stakeholder type' },
                                    { key: 'comment', label: 'Comment' },
                                    { key: 'updated_at', label: 'Updated' },
                                ],

                                init() {
                                    // load visible columns from localStorage
                                    try {
                                        const raw = localStorage.getItem(STORAGE_KEY);
                                        if (raw) {
                                            const parsed = JSON.parse(raw);
                                            if (parsed && typeof parsed === 'object') {
                                                this.visible = { ...defaultVisible, ...parsed };
                                            }
                                        }
                                    } catch (e) {
                                        // ignore
                                    }

                                    // ensure select-all indeterminate is correct on load
                                    this.syncSelectAllState();
                                },

                                persistColumns() {
                                    try {
                                        localStorage.setItem(STORAGE_KEY, JSON.stringify(this.visible));
                                    } catch (e) {
                                        // ignore
                                    }
                                },

                                isVisible(key) {
                                    return this.visible[key] !== false;
                                },

                                toggleColumn(key) {
                                    this.visible[key] = !this.isVisible(key);
                                    this.persistColumns();
                                },

                                resetColumns() {
                                    this.visible = { ...defaultVisible };
                                    this.persistColumns();
                                },

                                // -------- bulk selection (page only) --------
                                isSelected(id) {
                                    return this.selectedIds.includes(id);
                                },

                                toggleRow(id) {
                                    if (this.isSelected(id)) {
                                        this.selectedIds = this.selectedIds.filter(x => x !== id);
                                    } else {
                                        this.selectedIds = [...this.selectedIds, id];
                                    }
                                    this.syncSelectAllState();
                                },

                                selectedCountOnPage() {
                                    const set = new Set(this.selectedIds);
                                    return this.pageIds.filter(id => set.has(id)).length;
                                },

                                allPageSelected() {
                                    if (this.pageIds.length === 0) return false;
                                    return this.pageIds.every(id => this.selectedIds.includes(id));
                                },

                                toggleAllPage(checked) {
                                    if (!checked) {
                                        this.selectedIds = this.selectedIds.filter(id => !this.pageIds.includes(id));
                                        this.syncSelectAllState();
                                        return;
                                    }

                                    const set = new Set(this.selectedIds);
                                    this.pageIds.forEach(id => set.add(id));
                                    this.selectedIds = Array.from(set);
                                    this.syncSelectAllState();
                                },

                                syncSelectAllState() {
                                    // indeterminate when some (but not all) on this page are selected
                                    if (!this.$refs || !this.$refs.selectAll) return;

                                    const selectedOnPage = this.selectedCountOnPage();
                                    this.$refs.selectAll.indeterminate =
                                        selectedOnPage > 0 && selectedOnPage < this.pageIds.length;
                                },
                            }
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
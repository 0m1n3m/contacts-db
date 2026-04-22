<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Contact #{{ $contact->id }}
            </h2>

            @if (auth()->user()?->role && in_array(auth()->user()->role, ['admin','editor'], true))
                <a class="underline" href="{{ route('contacts.edit', $contact) }}">Edit</a>
            @endif

            @if (auth()->user()?->role === 'admin')
                <div x-data="{ open: false }" class="inline-block">
                    <button type="button" class="underline text-red-700" @click="open = true">
                        Delete
                    </button>

                    <!-- Modal backdrop -->
                    <div x-show="open"
                        x-transition.opacity
                        class="fixed inset-0 bg-black/50 z-50"
                        @click="open = false"
                        aria-hidden="true">
                    </div>

                    <!-- Modal panel -->
                    <div x-show="open"
                        x-transition
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        aria-modal="true"
                        role="dialog">
                        <div class="w-full max-w-md rounded bg-white shadow-lg p-6"
                            @click.stop>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Delete contact?
                            </h3>

                            <p class="mt-2 text-sm text-gray-600">
                                This action cannot be undone.
                            </p>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button"
                                        class="px-4 py-2 border rounded"
                                        @click="open = false">
                                    Cancel
                                </button>

                                <form method="POST" action="{{ route('contacts.destroy', $contact) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-4 py-2 rounded bg-red-700 text-white">
                                        Yes, delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <a class="underline" href="{{ route('contacts.index') }}">Back</a>
        </div>
    </x-slot>

    @php
        $countryName = $contact->country ? (config('countries')[$contact->country] ?? $contact->country) : null;

        $emails = $contact->emails ?? [];
        $phones = $contact->phones ?? [];
        $orgTypes = $contact->organisation_types ?? [];
        $keywords = $contact->keywords ?? [];
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">

                    <div>
                        <div class="text-sm text-gray-600">Name</div>
                        <div class="font-semibold">
                            {{ trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) ?: '(No name)' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-600">Organisation</div>
                            <div>{{ $contact->organisation_name ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-600">Job title</div>
                            <div>{{ $contact->job_title ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-600">Contact category</div>
                            <div>{{ $contact->contact_category }}</div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-600">Relationship status</div>
                            <div>{{ $contact->relationship_status }}</div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-600">Country</div>
                            <div>{{ $countryName ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-600">Flags</div>
                            <div>
                                Use for events: <strong>{{ $contact->use_for_events ? 'Yes' : 'No' }}</strong><br>
                                Potential speaker: <strong>{{ $contact->potential_speaker ? 'Yes' : 'No' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-600">Emails</div>
                            @if (count($emails))
                                <ul class="list-disc ml-5">
                                    @foreach ($emails as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>—</div>
                            @endif
                        </div>

                        <div>
                            <div class="text-sm text-gray-600">Phones</div>
                            @if (count($phones))
                                <ul class="list-disc ml-5">
                                    @foreach ($phones as $p)
                                        <li>{{ $p }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>—</div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-600">Organisation types</div>
                            @if (count($orgTypes))
                                <ul class="list-disc ml-5">
                                    @foreach ($orgTypes as $t)
                                        <li>{{ $t }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>—</div>
                            @endif
                        </div>

                        <div>
                            <div class="text-sm text-gray-600">Keywords</div>
                            @if (count($keywords))
                                <ul class="list-disc ml-5">
                                    @foreach ($keywords as $k)
                                        <li>{{ $k }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>—</div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-600">Relevant project / programme</div>
                        <div>{{ $contact->relevant_project_programme ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-600">Expertise / speaking topics</div>
                        <div class="whitespace-pre-line">{{ $contact->expertise_speaking_topics ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-600">Stakeholder type</div>
                        <div>{{ $contact->stakeholder_type ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-600">Comment</div>
                        <div class="whitespace-pre-line">{{ $contact->comment ?? '—' }}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
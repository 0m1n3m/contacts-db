{{-- resources/views/contacts/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Contacts
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (auth()->user()?->role && in_array(auth()->user()->role, ['admin','editor'], true))
                        <div class="mb-4">
                            <a class="underline" href="{{ route('contacts.create') }}">Create contact</a>
                        </div>
                    @endif

                    @if (auth()->user()?->role && in_array(auth()->user()->role, ['admin','editor'], true))
                        <div class="mb-4">
                            <div class="text-sm text-gray-500">
                                {{ route('contacts.import.create') }}
                            </div>
                            <a class="underline" href="{{ route('contacts.import.create') }}">Import contacts</a>
                        </div>
                    @endif

                    <div class="mb-4">
                        <strong>Total:</strong> {{ $contacts->total() }}
                    </div>

                    <div class="space-y-2">
                        @forelse ($contacts as $contact)
                            @php
                                $countryName = $contact->country ? (config('countries')[$contact->country] ?? $contact->country) : null;
                            @endphp
                            <a href="{{ route('contacts.show', $contact) }}" class="block border rounded p-3 hover:bg-gray-50">
                                <div class="font-semibold">
                                    {{ trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) ?: '(No name)' }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ $contact->organisation_name ?? '—' }}
                                    @if ($countryName)
                                        · {{ $countryName }}
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="text-gray-600">
                                No contacts yet.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $contacts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
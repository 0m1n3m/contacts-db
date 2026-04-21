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
                    @if (auth()->user()?->role && in_array(auth()->user()->role, ['admin','editor'], true))
                        <div class="mb-4">
                            <a class="underline" href="{{ route('contacts.create') }}">Create contact</a>
                        </div>
                    @endif
                    <div class="mb-4">
                        <strong>Total:</strong> {{ $contacts->total() }}
                    </div>

                    <div class="space-y-2">
                        @forelse ($contacts as $contact)
                            <div class="border rounded p-3">
                                <div class="font-semibold">
                                    {{ trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) ?: '(No name)' }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ $contact->organisation_name ?? '—' }}
                                </div>
                            </div>
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
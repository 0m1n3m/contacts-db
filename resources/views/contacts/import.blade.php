<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Import Contacts
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">

                    @if (session('status'))
                        <div class="rounded border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contacts.import.preview') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block font-medium">File (CSV or Excel)</label>
                            <input type="file" name="file" required>
                            @error('file') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex items-center gap-3">
                            <button class="px-4 py-2 bg-black text-white rounded" type="submit">
                                Upload & Preview
                            </button>

                            <a class="underline" href="{{ route('contacts.index') }}">
                                Back
                            </a>
                        </div>
                    </form>

                    <div class="text-sm text-gray-600">
                        Recommended format:
                        <ul class="list-disc ml-5 mt-1">
                            <li>Use ISO country codes (ES, US, ...)</li>
                            <li>For list fields (emails/phones/keywords/organisation_types), separate items with <strong>;</strong></li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
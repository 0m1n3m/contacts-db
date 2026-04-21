<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Contact
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('contacts.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block font-medium">Contact category *</label>
                            <input name="contact_category" class="border rounded w-full" required value="{{ old('contact_category') }}">
                            @error('contact_category') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium">Relationship status *</label>
                            <input name="relationship_status" class="border rounded w-full" required value="{{ old('relationship_status') }}">
                            @error('relationship_status') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex gap-6">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="use_for_events" value="1" @checked(old('use_for_events'))>
                                <span>Use for events</span>
                            </label>

                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="potential_speaker" value="1" @checked(old('potential_speaker'))>
                                <span>Potential speaker</span>
                            </label>
                        </div>

                        <div>
                            <label class="block font-medium">Organisation name</label>
                            <input name="organisation_name" class="border rounded w-full" value="{{ old('organisation_name') }}">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium">First name</label>
                                <input name="first_name" class="border rounded w-full" value="{{ old('first_name') }}">
                            </div>
                            <div>
                                <label class="block font-medium">Last name</label>
                                <input name="last_name" class="border rounded w-full" value="{{ old('last_name') }}">
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium">Job title</label>
                            <input name="job_title" class="border rounded w-full" value="{{ old('job_title') }}">
                        </div>

                        <div>
                            <label class="block font-medium">Emails (one per line)</label>
                            <textarea name="emails_text" class="border rounded w-full" rows="3">{{ old('emails_text') }}</textarea>
                        </div>

                        <div>
                            <label class="block font-medium">Phones (one per line)</label>
                            <textarea name="phones_text" class="border rounded w-full" rows="3">{{ old('phones_text') }}</textarea>
                        </div>

                        <div>
                            <button class="px-4 py-2 bg-black text-white rounded" type="submit">
                                Save
                            </button>
                            <a class="ml-3 underline" href="{{ route('contacts.index') }}">Cancel</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
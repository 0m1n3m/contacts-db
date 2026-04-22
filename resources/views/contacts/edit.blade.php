<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Contact #{{ $contact->id }}
        </h2>
    </x-slot>

    @php
        $emailsText = old('emails_text', implode("\n", $contact->emails ?? []));
        $phonesText = old('phones_text', implode("\n", $contact->phones ?? []));
        $orgTypesText = old('organisation_types_text', implode("\n", $contact->organisation_types ?? []));
        $keywordsText = old('keywords_text', implode("\n", $contact->keywords ?? []));
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('contacts.update', $contact) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block font-medium">Contact category *</label>
                            <input name="contact_category" class="border rounded w-full" required
                                value="{{ old('contact_category', $contact->contact_category) }}">
                            @error('contact_category') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium">Relationship status *</label>
                            <input name="relationship_status" class="border rounded w-full" required
                                value="{{ old('relationship_status', $contact->relationship_status) }}">
                            @error('relationship_status') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex gap-6">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="use_for_events" value="1"
                                    @checked(old('use_for_events', $contact->use_for_events))>
                                <span>Use for events</span>
                            </label>

                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="potential_speaker" value="1"
                                    @checked(old('potential_speaker', $contact->potential_speaker))>
                                <span>Potential speaker</span>
                            </label>
                        </div>

                        <div>
                            <label class="block font-medium">Organisation name</label>
                            <input name="organisation_name" class="border rounded w-full"
                                value="{{ old('organisation_name', $contact->organisation_name) }}">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium">First name</label>
                                <input name="first_name" class="border rounded w-full"
                                    value="{{ old('first_name', $contact->first_name) }}">
                            </div>
                            <div>
                                <label class="block font-medium">Last name</label>
                                <input name="last_name" class="border rounded w-full"
                                    value="{{ old('last_name', $contact->last_name) }}">
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium">Country</label>
                            <select name="country" class="border rounded w-full">
                                <option value="">—</option>
                                @foreach (config('countries') as $code => $name)
                                    <option value="{{ $code }}" @selected(old('country', $contact->country) === $code)>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium">Job title</label>
                            <input name="job_title" class="border rounded w-full"
                                value="{{ old('job_title', $contact->job_title) }}">
                        </div>

                        <div>
                            <label class="block font-medium">Emails (one per line)</label>
                            <textarea name="emails_text" class="border rounded w-full" rows="3">{{ $emailsText }}</textarea>
                        </div>

                        <div>
                            <label class="block font-medium">Phones (one per line)</label>
                            <textarea name="phones_text" class="border rounded w-full" rows="3">{{ $phonesText }}</textarea>
                        </div>

                        <div>
                            <label class="block font-medium">Organisation types (one per line)</label>
                            <textarea name="organisation_types_text" class="border rounded w-full" rows="3">{{ $orgTypesText }}</textarea>
                        </div>

                        <div>
                            <label class="block font-medium">Keywords (one per line)</label>
                            <textarea name="keywords_text" class="border rounded w-full" rows="3">{{ $keywordsText }}</textarea>
                        </div>

                        <div>
                            <label class="block font-medium">Relevant project / programme</label>
                            <input name="relevant_project_programme" class="border rounded w-full"
                                value="{{ old('relevant_project_programme', $contact->relevant_project_programme) }}">
                        </div>

                        <div>
                            <label class="block font-medium">Expertise / speaking topics</label>
                            <textarea name="expertise_speaking_topics" class="border rounded w-full" rows="4">{{ old('expertise_speaking_topics', $contact->expertise_speaking_topics) }}</textarea>
                        </div>

                        <div>
                            <label class="block font-medium">Stakeholder type</label>
                            <input name="stakeholder_type" class="border rounded w-full"
                                value="{{ old('stakeholder_type', $contact->stakeholder_type) }}">
                        </div>

                        <div>
                            <label class="block font-medium">Comment</label>
                            <textarea name="comment" class="border rounded w-full" rows="4">{{ old('comment', $contact->comment) }}</textarea>
                        </div>

                        <div>
                            <button class="px-4 py-2 bg-black text-white rounded" type="submit">
                                Update
                            </button>
                            <a class="ml-3 underline" href="{{ route('contacts.show', $contact) }}">Cancel</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
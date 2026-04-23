<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Import Contacts — Map columns
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div class="text-sm text-gray-600">
                        File detected columns:
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($columns as $c)
                                <span class="px-2 py-1 rounded bg-gray-100 border text-gray-700">{{ $c }}</span>
                            @endforeach
                        </div>
                    </div>

                    <form method="POST" action="{{ route('contacts.import.run') }}" class="space-y-4">
                        @csrf

                        <input type="hidden" name="stored_path" value="{{ $storedPath }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($fields as $field => $label)
                                <div>
                                    <label class="block font-medium">{{ $label }}</label>
                                    <select name="map[{{ $field }}]" class="border rounded w-full">
                                        <option value="">— not mapped —</option>
                                        @foreach ($columns as $c)
                                            <option value="{{ $c }}" @selected(($autoMap[$field] ?? '') === $c)>
                                                {{ $c }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-3">
                            <button class="px-4 py-2 bg-black text-white rounded" type="submit">
                                Import
                            </button>

                            <a class="underline" href="{{ route('contacts.import.create') }}">
                                Back
                            </a>
                        </div>
                    </form>

                    <div>
                        <h3 class="font-semibold">Preview (first {{ count($previewRows) }} rows)</h3>

                        <div class="mt-2 overflow-auto border rounded">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @foreach ($columns as $c)
                                            <th class="text-left px-3 py-2 border-b">{{ $c }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($previewRows as $row)
                                        <tr class="odd:bg-white even:bg-gray-50">
                                            @foreach ($columns as $c)
                                                <td class="px-3 py-2 border-b align-top">
                                                    {{ $row[$c] ?? '' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-sm text-gray-600">
                            Tip: for list fields (emails/phones/keywords/organisation_types) separate items with <strong>;</strong>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
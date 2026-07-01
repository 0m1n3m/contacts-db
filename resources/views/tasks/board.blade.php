{{-- resources/views/tasks/board.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tasks
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-0">
                    <div
                        id="tasks-board"
                        data-columns='@json($columns)'
                        data-auth='@json($auth)'
                    ></div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
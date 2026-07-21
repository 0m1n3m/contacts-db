<div class="space-y-4 border-t pt-4">
    <h3 class="text-lg font-semibold">Dependencias</h3>

    <!-- Tareas de las que depende -->
    <div>
        <h4 class="font-medium text-gray-700 mb-2">Depende de:</h4>
        @forelse($task->dependencies as $dep)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded mb-2">
                <div class="flex-1">
                    <a href="{{ route('tasks.show', $dep->dependentTask) }}" class="text-blue-600 hover:underline">
                        {{ $dep->dependentTask->title }}
                    </a>
                    <span class="text-sm text-gray-500 ml-2">
                        ({{ $dep->dependentTask->status }})
                    </span>
                </div>
                <span class="text-xs bg-gray-200 px-2 py-1 rounded">{{ $dep->type }}</span>
                @can('delete', $dep)
                    <form action="{{ route('tasks.dependencies.destroy', [$task, $dep]) }}" 
                          method="POST" 
                          class="ml-2"
                          onsubmit="return confirm('¿Eliminar dependencia?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>
                @endcan
            </div>
        @empty
            <p class="text-gray-500 text-sm">No tiene dependencias</p>
        @endforelse
    </div>

    <!-- Subtareas (tareas que dependen de esta) -->
    <div class="border-t pt-4">
        <h4 class="font-medium text-gray-700 mb-2">Tareas que dependen de esta:</h4>
        @forelse($task->subtasks as $sub)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded mb-2">
                <div class="flex-1">
                    <a href="{{ route('tasks.show', $sub->task) }}" class="text-blue-600 hover:underline">
                        {{ $sub->task->title }}
                    </a>
                    <span class="text-sm text-gray-500 ml-2">
                        ({{ $sub->task->status }})
                    </span>
                </div>
                <span class="text-xs bg-gray-200 px-2 py-1 rounded">{{ $sub->type }}</span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">No hay tareas dependientes</p>
        @endforelse
    </div>

    <!-- Crear dependencia -->
    @can('create', [App\Models\TaskDependency::class, $task])
        <details class="border-t pt-4">
            <summary class="cursor-pointer font-semibold text-gray-700">+ Agregar dependencia</summary>
            <form action="{{ route('tasks.dependencies.store', $task) }}" method="POST" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tarea que se requiere:</label>
                    <select name="dependent_task_id" class="w-full rounded border-gray-300" required>
                        <option value="">Seleccionar tarea...</option>
                        @foreach($allTasks as $t)
                            @if($t->id !== $task->id)
                                <option value="{{ $t->id }}">{{ $t->title }} ({{ $t->status }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo:</label>
                    <select name="type" class="w-full rounded border-gray-300">
                        <option value="depends_on">Depende de</option>
                        <option value="blocks">Bloquea</option>
                        <option value="relates_to">Relacionada a</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Agregar Dependencia
                </button>
            </form>
        </details>
    @endcan
</div>
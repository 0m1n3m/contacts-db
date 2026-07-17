<div class="space-y-4">
    <div>
        <h3 class="text-lg font-semibold mb-3">Etiquetas</h3>
        
        <!-- Tags actuales -->
        <div class="flex flex-wrap gap-2 mb-4">
            @forelse($task->tags as $tag)
                <div class="flex items-center gap-2 px-3 py-1 rounded-full text-white text-sm" 
                     style="background-color: {{ $tag->color }}">
                    <span>{{ $tag->name }}</span>
                    @can('detachTag', $task)
                        <form action="{{ route('tasks.tags.detach', [$task, $tag]) }}" 
                              method="POST" 
                              class="inline"
                              onsubmit="return confirm('¿Remover esta etiqueta?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hover:opacity-75">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    @endcan
                </div>
            @empty
                <p class="text-gray-500">Sin etiquetas</p>
            @endforelse
        </div>

        <!-- Agregar tag -->
        @can('attachTag', $task)
            <form action="{{ route('tasks.tags.attach', $task) }}" method="POST" class="flex gap-2">
                @csrf
                <select name="tag_id" class="flex-1 rounded border-gray-300" required>
                    <option value="">Seleccionar etiqueta...</option>
                    @foreach($availableTags as $tag)
                        @if(!$task->tags->contains($tag->id))
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endif
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Agregar
                </button>
            </form>
        @endcan
    </div>

    <!-- Crear nuevo tag -->
    @can('create', App\Models\Tag::class)
        <details class="border-t pt-4">
            <summary class="cursor-pointer font-semibold text-gray-700">+ Crear nueva etiqueta</summary>
            <form action="{{ route('tags.store') }}" method="POST" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="name" class="w-full rounded border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Color</label>
                    <input type="color" name="color" class="h-10 w-20 rounded border-gray-300" value="#3B82F6">
                </div>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Crear Etiqueta
                </button>
            </form>
        </details>
    @endcan
</div>
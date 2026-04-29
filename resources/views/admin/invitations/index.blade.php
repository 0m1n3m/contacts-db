<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Invitaciones
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="p-3 mb-4 bg-green-100 border border-green-200 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="p-4 bg-white shadow sm:rounded-lg mb-6">
                <form method="POST" action="{{ route('admin.invitations.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium">Email</label>
                        <input name="email" type="email" class="border rounded w-full p-2" required>
                        @error('email') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Role</label>
                        <select name="role" class="border rounded w-full p-2" required>
                            <option value="viewer">viewer</option>
                            <option value="editor">editor</option>
                            <option value="admin">admin</option>
                        </select>
                        @error('role') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <button class="px-4 py-2 bg-black text-white rounded" type="submit">
                        Enviar invitación
                    </button>
                </form>
            </div>

            <div class="p-4 bg-white shadow sm:rounded-lg">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="py-2">Email</th>
                            <th class="py-2">Role</th>
                            <th class="py-2">Expira</th>
                            <th class="py-2">Aceptada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invitations as $inv)
                            <tr class="border-t">
                                <td class="py-2">{{ $inv->email }}</td>
                                <td class="py-2">{{ $inv->role }}</td>
                                <td class="py-2">{{ optional($inv->expires_at)->format('Y-m-d H:i') }}</td>
                                <td class="py-2">{{ $inv->accepted_at ? $inv->accepted_at->format('Y-m-d H:i') : 'no' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $invitations->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<?php

namespace App\Http\Controllers;

use App\Actions\Tags\CreateTagAction;
use App\Actions\Tags\UpdateTagAction;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::all();
        return response()->json($tags);
    }

    public function store(Request $request, CreateTagAction $action): JsonResponse|RedirectResponse
    {
        // Solo admin/editor pueden crear tags
        if (!in_array(auth()->user()->role ?? 'viewer', ['admin', 'editor'])) {
            abort(403, 'You cannot create tags.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $tag = $action->execute(
            name: $validated['name'],
            color: $validated['color'] ?? '#3B82F6',
        );

        if ($request->wantsJson()) {
            return response()->json($tag, 201);
        }

        // Redirige a la tarea anterior (si viene de una tarea)
        return back()->with('success', 'Etiqueta creada exitosamente');
    }

    public function update(Request $request, Tag $tag, UpdateTagAction $action): JsonResponse|RedirectResponse
    {
        // Solo admin/editor pueden editar
        if (!in_array(auth()->user()->role ?? 'viewer', ['admin', 'editor'])) {
            abort(403, 'You cannot update tags.');
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $tag = $action->execute(
            tag: $tag,
            name: $validated['name'] ?? null,
            color: $validated['color'] ?? null,
        );

        if ($request->wantsJson()) {
            return response()->json($tag);
        }

        return back()->with('success', 'Etiqueta actualizada');
    }

    public function destroy(Tag $tag, Request $request): JsonResponse|RedirectResponse
    {
        // Solo admin puede eliminar
        if ((auth()->user()->role ?? 'viewer') !== 'admin') {
            abort(403, 'You cannot delete tags.');
        }

        $tag->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Etiqueta eliminada');
    }
}
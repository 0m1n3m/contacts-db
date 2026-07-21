<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Listar proyectos
     */
    public function index()
    {
        $projects = Project::with(['client', 'creator'])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('projects.index', compact('projects'));
    }

    /**
     * Mostrar formulario de crear proyecto
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();

        return view('projects.create', compact('clients'));
    }

    /**
     * Guardar nuevo proyecto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'client_id' => ['nullable', 'exists:clients,id'],
        ]);

        $project = Project::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('projects.show', $project)
            ->with('status', 'Proyecto creado exitosamente.');
    }

    /**
     * Mostrar proyecto
     */
    public function show(Project $project)
    {
        $project->load(['client', 'creator', 'tasks' => function ($query) {
            $query->orderByDesc('created_at');
        }]);

        // Estadísticas del proyecto
        $stats = [
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('status', 'done')->count(),
            'in_progress_tasks' => $project->tasks()->where('status', 'in_progress')->count(),
            'in_review_tasks' => $project->tasks()->where('status', 'in_review')->count(),
            'avg_lead_time' => $project->tasks()
                ->where('status', 'done')
                ->avg('lead_time'),
        ];

        return view('projects.show', compact('project', 'stats'));
    }

    /**
     * Mostrar formulario de editar proyecto
     */
    public function edit(Project $project)
    {
        $clients = Client::orderBy('name')->get();
        
        return view('projects.edit', compact('project', 'clients'));
    }

    /**
     * Actualizar proyecto
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'status' => ['required', 'in:active,archived'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('status', 'Proyecto actualizado exitosamente.');
    }

    /**
     * Eliminar proyecto
     */
    public function destroy(Project $project)
    {
        // Verificar que no tenga tareas asociadas
        if ($project->tasks()->count() > 0) {
            return back()->with('error', 'No puedes eliminar un proyecto que tiene tareas asociadas. Archívalo en su lugar.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('status', 'Proyecto eliminado exitosamente.');
    }
}
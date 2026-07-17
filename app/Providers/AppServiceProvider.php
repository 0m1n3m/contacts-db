<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Tag;
use App\Models\TaskDependency;
use App\Policies\TagPolicy;
use App\Policies\TaskTagPolicy;
use App\Policies\TaskDependencyPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Registrar policies
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(TaskDependency::class, TaskDependencyPolicy::class);

        Task::observe(TaskObserver::class);
    }
}
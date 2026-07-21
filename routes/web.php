<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\TaskBoardController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskMoveController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskTagController;
use App\Http\Controllers\TaskDependencyController;
use App\Http\Controllers\Admin\UserInvitationController;
use App\Http\Controllers\Auth\AcceptInvitationController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('contacts.index');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'role:admin,editor'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/contacts', [ContactController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('contacts.index');

Route::get('/contacts/import', [ContactImportController::class, 'create'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.import.create');

Route::post('/contacts/import/preview', [ContactImportController::class, 'preview'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.import.preview');

Route::post('/contacts/import/run', [ContactImportController::class, 'run'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.import.run');

Route::post('/contacts/export', [ContactController::class, 'export'])
    ->middleware(['auth', 'verified'])
    ->name('contacts.export');

Route::get('/contacts/create', [ContactController::class, 'create'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.create');

Route::post('/contacts', [ContactController::class, 'store'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.store');

Route::post('/contacts/bulk-destroy', [ContactController::class, 'bulkDestroy'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('contacts.bulk-destroy');

Route::get('/contacts/{contact}', [ContactController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('contacts.show');

Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.edit');

Route::patch('/contacts/{contact}', [ContactController::class, 'update'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.update');

Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('contacts.destroy');

// Admin - invitaciones
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/invitations', [UserInvitationController::class, 'index'])
        ->name('admin.invitations.index');

    Route::post('/admin/invitations', [UserInvitationController::class, 'store'])
        ->name('admin.invitations.store');
});

// Público - aceptar invitación
Route::get('/invitations/accept', [AcceptInvitationController::class, 'show'])
    ->name('invitations.accept.show');

Route::post('/invitations/accept', [AcceptInvitationController::class, 'store'])
    ->name('invitations.accept.store');

require __DIR__.'/auth.php';

//Tasks
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tasks/board', [TaskBoardController::class, 'index'])
    ->name('tasks.board');

    Route::get('/tasks', [TaskController::class, 'index'])
        ->name('tasks.index');

    Route::get('/tasks/create', [TaskController::class, 'create'])
        ->middleware('role:admin,editor')
        ->name('tasks.create');

    Route::post('/tasks', [TaskController::class, 'store'])
        ->middleware('role:admin,editor')
        ->name('tasks.store');

    Route::post('/tasks/assignments/{assignment}/accept', [TaskAssignmentController::class, 'accept'])
    ->name('tasks.assignments.accept');

    Route::post('/tasks/assignments/{assignment}/reject', [TaskAssignmentController::class, 'reject'])
        ->name('tasks.assignments.reject');

    Route::delete('/tasks/assignments/{assignment}', [TaskAssignmentController::class, 'destroy'])
        ->name('tasks.assignments.destroy');

    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->name('tasks.show');

    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])
        ->middleware('role:admin,editor')
        ->name('tasks.edit');

    Route::patch('/tasks/{task}', [TaskController::class, 'update'])
        ->middleware('role:admin,editor')
        ->name('tasks.update');

    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->middleware('role:admin,editor')
        ->name('tasks.destroy');

    Route::patch('/tasks/{task}/move', TaskMoveController::class)
    ->name('tasks.move');

    // Task Comments
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])
        ->name('tasks.comments.store');

    Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])
        ->name('tasks.comments.destroy');

    // Task Attachments
    Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])
        ->name('tasks.attachments.store');

    Route::get('/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])
        ->name('tasks.attachments.download');

    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])
        ->name('tasks.attachments.destroy');

    // Task Status Changes
    Route::patch('/tasks/{task}/change-status', [TaskController::class, 'changeStatus'])
    ->name('tasks.change-status');

    // Notifications - Web Routes
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unreadCount');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.markAsRead');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllAsRead');

    Route::post('/notifications/mark-all-read-ajax', [NotificationController::class, 'markAllAsReadAjax'])
    ->name('notifications.markAllAsReadAjax');

    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    // Notifications - AJAX API (para el dropdown)
    Route::get('/api/notifications/recent', [NotificationController::class, 'getRecent'])
        ->name('api.notifications.recent');
});

// Projects
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])
        ->name('projects.index');

    Route::get('/projects/create', [ProjectController::class, 'create'])
        ->middleware('role:admin,editor')
        ->name('projects.create');

    Route::post('/projects', [ProjectController::class, 'store'])
        ->middleware('role:admin,editor')
        ->name('projects.store');

    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show');

    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])
        ->middleware('role:admin,editor')
        ->name('projects.edit');

    Route::patch('/projects/{project}', [ProjectController::class, 'update'])
        ->middleware('role:admin,editor')
        ->name('projects.update');

    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
        ->middleware('role:admin,editor')
        ->name('projects.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // ===== TAGS (Blade) =====
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::patch('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

    // ===== TASK TAGS (Blade) =====
    Route::post('/tasks/{task}/tags', [TaskTagController::class, 'attach'])->name('tasks.tags.attach');
    Route::delete('/tasks/{task}/tags/{tag}', [TaskTagController::class, 'detach'])->name('tasks.tags.detach');

    // ===== TASK DEPENDENCIES (Blade) =====
    Route::post('/tasks/{task}/dependencies', [TaskDependencyController::class, 'store'])->name('tasks.dependencies.store');
    Route::delete('/tasks/{task}/dependencies/{dependency}', [TaskDependencyController::class, 'destroy'])->name('tasks.dependencies.destroy');
});

// ===== API ROUTES =====
Route::middleware(['auth', 'verified'])->prefix('api')->group(function () {
    // ===== TAGS =====
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index']);                    // GET  /api/tags
        Route::post('/', [TagController::class, 'store']);                   // POST /api/tags
        Route::patch('/{tag}', [TagController::class, 'update']);            // PATCH /api/tags/{tag}
        Route::delete('/{tag}', [TagController::class, 'destroy']);          // DELETE /api/tags/{tag}
    });

    // ===== TASK TAGS =====
    Route::prefix('tasks/{task}/tags')->group(function () {
        Route::get('/', [TaskTagController::class, 'getTags']);              // GET /api/tasks/{task}/tags
        Route::post('/{tag}', [TaskTagController::class, 'attach']);         // POST /api/tasks/{task}/tags/{tag}
        Route::delete('/{tag}', [TaskTagController::class, 'detach']);       // DELETE /api/tasks/{task}/tags/{tag}
    });

    // ===== TASK DEPENDENCIES =====
    Route::prefix('tasks/{task}/dependencies')->group(function () {
        Route::get('/', [TaskDependencyController::class, 'getDependencies']); // GET /api/tasks/{task}/dependencies
        Route::post('/', [TaskDependencyController::class, 'store']);          // POST /api/tasks/{task}/dependencies
        Route::delete('/{dependency}', [TaskDependencyController::class, 'destroy']); // DELETE /api/tasks/{task}/dependencies/{dependency}
    });
});
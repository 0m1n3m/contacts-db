<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;
use App\Http\Controllers\Admin\UserInvitationController;
use App\Http\Controllers\Auth\AcceptInvitationController;
use App\Http\Controllers\TaskBoardController;
use App\Http\Controllers\TaskMoveController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\NotificationController;

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

    // Notifications - Web Routes
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unreadCount');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.markAsRead');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllAsRead');

    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    // Notifications - Web Routes
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unreadCount');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.markAsRead');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllAsRead');

    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    // Notifications - AJAX API (para el dropdown)
    Route::get('/api/notifications/recent', [NotificationController::class, 'getRecent'])
        ->name('api.notifications.recent');
});
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;
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

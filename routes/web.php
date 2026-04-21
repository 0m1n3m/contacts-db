<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::get('/', function () {
    return view('welcome');
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

Route::get('/contacts/create', [ContactController::class, 'create'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.create');

Route::post('/contacts', [ContactController::class, 'store'])
    ->middleware(['auth', 'verified', 'role:admin,editor'])
    ->name('contacts.store');

require __DIR__.'/auth.php';

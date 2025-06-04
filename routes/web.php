<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DigitalBookController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth;

// Home route
Route::get('/', fn() => redirect()->route('digital-books.index'));

// Authentication Routes
Route::namespace('Auth')->middleware('guest')->group(function () {
    Route::get('login', [Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [Auth\LoginController::class, 'login']);
    Route::get('register', [Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [Auth\RegisterController::class, 'register']);
    Route::get('password/reset', [Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout Route
Route::post('logout', [Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Digital Books Routes
Route::prefix('digital-books')->name('digital-books.')->middleware('auth')->group(function () {
    Route::get('/', [DigitalBookController::class, 'index'])->name('index');
    Route::get('/{digitalBook}', [DigitalBookController::class, 'show'])->name('show');
    Route::get('/{digitalBook}/read', [DigitalBookController::class, 'read'])->name('read');
    Route::get('/{digitalBook}/pdf', [DigitalBookController::class, 'servePdf'])->name('pdf');
    Route::post('/{digitalBook}/progress', [DigitalBookController::class, 'updateProgress'])->name('progress');

    Route::middleware('can:create,App\Models\DigitalBook')->group(function () {
        Route::get('/create', [DigitalBookController::class, 'create'])->name('create');
        Route::post('/', [DigitalBookController::class, 'store'])->name('store');
    });

    Route::middleware('can:update,digitalBook')->group(function () {
        Route::get('/{digitalBook}/edit', [DigitalBookController::class, 'edit'])->name('edit');
        Route::put('/{digitalBook}', [DigitalBookController::class, 'update'])->name('update');
    });

    Route::middleware('can:delete,digitalBook')->group(function () {
        Route::delete('/{digitalBook}', [DigitalBookController::class, 'destroy'])->name('destroy');
    });
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/books', [AdminController::class, 'books'])->name('books');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
});

// Fallback
Route::fallback(fn() => view('errors.404'));

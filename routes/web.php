<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingSectionController;
use App\Http\Controllers\QueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QueueController::class, 'home'])->name('home');
Route::get('/jadwal', [QueueController::class, 'schedule'])->name('schedule.public');
Route::get('/ambil-antrean', [QueueController::class, 'create'])->name('queue.create');
Route::post('/ambil-antrean', [QueueController::class, 'store'])->middleware('throttle:10,1')->name('queue.store');
Route::get('/antrean/{ticket:tracking_token}', [QueueController::class, 'show'])->name('queue.show');
Route::get('/antrean/{ticket:tracking_token}/status', [QueueController::class, 'status'])->name('queue.status');
Route::get('/antrean/{ticket:tracking_token}/laporan.pdf', [QueueController::class, 'downloadReport'])->name('queue.report');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::post('/admin/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/antrean', [AdminController::class, 'queues'])->name('queues');
    Route::post('/antrean/next', [AdminController::class, 'callNext'])->name('queues.next');
    Route::patch('/antrean/{ticket}/status', [AdminController::class, 'updateStatus'])->name('queues.status');
    Route::get('/jadwal', [AdminController::class, 'editSchedule'])->name('schedule');
    Route::put('/jadwal', [AdminController::class, 'saveSchedule'])->name('schedule.save');
    Route::get('/informasi', [LandingSectionController::class, 'index'])->name('landing');
    Route::put('/informasi/{section}', [LandingSectionController::class, 'update'])->name('landing.update');
    Route::get('/laporan', [AdminController::class, 'report'])->name('report');
    Route::get('/laporan/export', [AdminController::class, 'export'])->name('report.export');
});

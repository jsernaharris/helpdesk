<?php

use App\Http\Controllers\Portal\ChangeRequestController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\KbChatController;
use App\Http\Controllers\Portal\KnowledgeBaseController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('portal')->name('portal.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::patch('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');

    // Change Requests
    Route::get('/changes', [ChangeRequestController::class, 'index'])->name('changes.index');
    Route::get('/changes/create', [ChangeRequestController::class, 'create'])->name('changes.create');
    Route::post('/changes', [ChangeRequestController::class, 'store'])->name('changes.store');
    Route::get('/changes/{change}', [ChangeRequestController::class, 'show'])->name('changes.show');

    // Knowledge Base
    Route::get('/kb', [KnowledgeBaseController::class, 'index'])->name('kb.index');
    Route::get('/kb/{category:slug}', [KnowledgeBaseController::class, 'category'])->name('kb.category');
    Route::get('/kb/{category:slug}/{article:slug}', [KnowledgeBaseController::class, 'show'])->name('kb.show');
    Route::post('/kb/chat', [KbChatController::class, 'ask'])->name('kb.chat');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

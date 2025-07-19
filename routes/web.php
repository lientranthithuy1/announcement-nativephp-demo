<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementController;

Route::get('/', [AnnouncementController::class, 'index'])->name('home');
Route::post('/add-announcement', [AnnouncementController::class, 'store'])->name('add.announcement');
Route::delete('/clear-seen', [AnnouncementController::class, 'clearSeen'])->name('clear.seen');
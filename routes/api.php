<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OfficeImageController;
use App\Http\Controllers\HostReservationController;
use App\Http\Controllers\UserReservationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Tags
Route::get('/tags', TagController::class);

// Offices
Route::get('/offices', [OfficeController::class, 'index'])->name('offices.index');
Route::get('/offices/{office}', [OfficeController::class, 'show'])->name('office.show');
Route::post('/offices', [OfficeController::class, 'create'])
    ->middleware(['auth:sanctum', 'verified'])->name('offices.create');
Route::put('/offices/{office}', [OfficeController::class, 'update'])
    ->middleware(['auth:sanctum', 'verified'])->name('offices.update');
Route::delete('/offices/{office}', [OfficeController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'verified'])->name('offices.delete');

//office photos
Route::post('/offices/{office}/images', [OfficeImageController::class, 'store'])
    ->middleware(['auth:sanctum', 'verified'])->name('offices.images.store');
Route::delete('/offices/{office}/images/{image:id}', [OfficeImageController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'verified'])->name('offices.images.delete');
//User Reservation
Route::get('/reservations', [UserReservationController::class, 'index'])->name('reservation.index');

//User Reservation
Route::get('/host/reservations', [HostReservationController::class, 'index'])->name('host.reservation.index');

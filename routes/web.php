<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotaController;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/nota/{transaksi}/print', [NotaController::class, 'print'])
        ->name('nota.print');
});
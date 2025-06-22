<?php

use A21ns1g4ts\FilamentCollections\Http\Controllers\CollectionContentController;
use Illuminate\Support\Facades\Route;

Route::get('api/filament-collections', [CollectionContentController::class, 'index'])
    ->middleware('api')
    ->name('filament-collections.index');

Route::post('api/filament-collections', [CollectionContentController::class, 'store'])
    ->middleware(['api', 'auth:sanctum', 'abilities:create'])
    ->name('filament-collections.store');

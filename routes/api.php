<?php

use A21ns1g4ts\FilamentCollections\Http\Controllers\CollectionContentController;
use Illuminate\Support\Facades\Route;

Route::get('api/collections', [CollectionContentController::class, 'index'])
    ->name('collections.index');

Route::post('api/collections', [CollectionContentController::class, 'store'])
    ->middleware(['auth:sanctum', 'abilities:create'])
    ->name('collections.store');

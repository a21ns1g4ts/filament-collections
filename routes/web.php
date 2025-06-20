<?php

use A21ns1g4ts\FilamentCollections\Http\Controllers\CollectionContentController;
use Illuminate\Support\Facades\Route;

Route::get('/filament-collections', [CollectionContentController::class, 'index'])
    ->middleware('web')
    ->name('filament-collections.index');

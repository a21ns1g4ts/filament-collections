<?php

use A21ns1g4ts\FilamentCollections\Http\Controllers\CollectionContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/collections')->as('collections.')->group(function () {
    Route::get('/{collectionKey}', [CollectionContentController::class, 'index'])->name('index');
    Route::post('/{collectionKey}', [CollectionContentController::class, 'store'])->name('store');
    Route::get('/{collectionKey}/{id}', [CollectionContentController::class, 'show'])->name('show');
    Route::put('/{collectionKey}/{id}', [CollectionContentController::class, 'update'])->name('update');
    Route::delete('/{collectionKey}/{id}', [CollectionContentController::class, 'destroy'])->name('destroy');
});
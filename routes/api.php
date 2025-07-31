<?php

use A21ns1g4ts\FilamentCollections\Http\Controllers\CollectionDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/collections')->as('collections.')->group(function () {
    Route::get('/{collectionKey}', [CollectionDataController::class, 'index'])->name('index');
    Route::post('/{collectionKey}', [CollectionDataController::class, 'store'])->name('store');
    Route::get('/{collectionKey}/{id}', [CollectionDataController::class, 'show'])->name('show');
    Route::put('/{collectionKey}/{id}', [CollectionDataController::class, 'update'])->name('update');
    Route::delete('/{collectionKey}/{id}', [CollectionDataController::class, 'destroy'])->name('destroy');
});
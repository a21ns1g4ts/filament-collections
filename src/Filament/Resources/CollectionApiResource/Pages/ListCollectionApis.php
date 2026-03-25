<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListCollectionApis extends ListRecords
{
    protected static string $resource = CollectionApiResource::class;

    public function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

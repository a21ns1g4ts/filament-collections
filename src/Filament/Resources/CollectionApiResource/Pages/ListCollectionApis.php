<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;

class ListCollectionApis extends ListRecords
{
    use HasClusterSubNavigation;
    protected static string $resource = CollectionApiResource::class;

    public function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

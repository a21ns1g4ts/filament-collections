<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource;
use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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

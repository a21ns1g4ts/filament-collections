<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;

class ListCollectionGroups extends ListRecords
{
    use HasClusterSubNavigation;
    protected static string $resource = CollectionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

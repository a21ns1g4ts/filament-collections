<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;

class ListCollectionConfigs extends ListRecords
{
    use HasClusterSubNavigation;
    use ExposesTableToWidgets;

    protected static string $resource = CollectionConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return CollectionConfigResource::getWidgets();
    }
}

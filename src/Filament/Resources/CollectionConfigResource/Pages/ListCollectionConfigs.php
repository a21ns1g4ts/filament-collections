<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource;
use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListCollectionConfigs extends ListRecords
{
    use ExposesTableToWidgets;
    use HasClusterSubNavigation;

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

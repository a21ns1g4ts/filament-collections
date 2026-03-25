<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource;
use Filament\Resources\Pages\CreateRecord;

use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;

class CreateCollectionConfigs extends CreateRecord
{
    use HasClusterSubNavigation;
    protected static string $resource = CollectionConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // \Filament\Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCollectionConfigs extends ManageRecords
{
    protected static string $resource = CollectionConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // \Filament\Actions\CreateAction::make(),
        ];
    }
}

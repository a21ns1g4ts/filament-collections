<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;

class EditCollectionGroup extends EditRecord
{
    use HasClusterSubNavigation;
    protected static string $resource = CollectionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

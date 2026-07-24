<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource;
use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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

<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;

class EditCollectionApi extends EditRecord
{
    use HasClusterSubNavigation;
    protected static string $resource = CollectionApiResource::class;

    public function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

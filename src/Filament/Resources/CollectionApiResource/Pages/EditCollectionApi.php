<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCollectionApi extends EditRecord
{
    protected static string $resource = CollectionApiResource::class;

    public function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

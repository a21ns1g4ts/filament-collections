<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource;
use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;
use Filament\Resources\Pages\CreateRecord;

class CreateCollectionApi extends CreateRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = CollectionApiResource::class;
}

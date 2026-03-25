<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource;
use Filament\Resources\Pages\CreateRecord;

use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;

class CreateCollectionApi extends CreateRecord
{
    use HasClusterSubNavigation;
    protected static string $resource = CollectionApiResource::class;
}

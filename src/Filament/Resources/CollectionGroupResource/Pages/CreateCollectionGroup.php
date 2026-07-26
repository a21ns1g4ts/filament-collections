<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource;
use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;
use Filament\Resources\Pages\CreateRecord;

class CreateCollectionGroup extends CreateRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = CollectionGroupResource::class;
}

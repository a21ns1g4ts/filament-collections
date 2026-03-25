<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource\Pages;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource;
use Filament\Resources\Pages\CreateRecord;

use A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns\HasClusterSubNavigation;

class CreateCollectionGroup extends CreateRecord
{
    use HasClusterSubNavigation;
    protected static string $resource = CollectionGroupResource::class;
}

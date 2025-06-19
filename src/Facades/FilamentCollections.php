<?php

namespace A21ns1g4ts\FilamentCollections\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \A21ns1g4ts\FilamentCollections\FilamentCollections
 */
class FilamentCollections extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \A21ns1g4ts\FilamentCollections\FilamentCollections::class;
    }
}

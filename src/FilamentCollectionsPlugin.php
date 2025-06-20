<?php

namespace A21ns1g4ts\FilamentCollections;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentCollectionsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-collections';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            Filament\Resources\CollectionConfigResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}

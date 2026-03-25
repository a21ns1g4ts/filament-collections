<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\Concerns;

trait HasClusterSubNavigation
{
    public function getSubNavigation(): array
    {
        if (method_exists($this, 'generateNavigationItems')) {
            return $this->generateNavigationItems(static::getResource()::getCluster()::getClusteredComponents());
        }

        return [];
    }

    public function generateNavigationItems(array $components): array
    {
        return collect($components)
            ->flatMap(fn (string $component) => $component::getNavigationItems())
            ->toArray();
    }
}

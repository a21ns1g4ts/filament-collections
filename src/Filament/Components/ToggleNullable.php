<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Components;

use Filament\Forms\Components\Toggle;

class ToggleNullable extends Toggle
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(static function (ToggleNullable $component, $state): void {
            $component->state($state);
        });

        $this->rule('boolean');
    }
}

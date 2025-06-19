<?php

namespace A21ns1g4ts\FilamentCollections\Commands;

use Illuminate\Console\Command;

class FilamentCollectionsCommand extends Command
{
    public $signature = 'filament-collections';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

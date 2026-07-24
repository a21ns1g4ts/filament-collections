<?php

namespace A21ns1g4ts\FilamentCollections\Commands;

use A21ns1g4ts\FilamentCollections\Support\FilamentCollectionsFactory;
use Illuminate\Console\Command;

class SeedCollectionsCommand extends Command
{
    protected $signature = 'filament-collections:seed {type? : The type of collections to seed (blog, cms)}';

    protected $description = 'Seed the collections with pre-configured templates.';

    public function handle(): int
    {
        $type = $this->argument('type');

        if (! $type) {
            $type = $this->choice(
                'Which template would you like to seed?',
                ['blog', 'cms'],
                0
            );
        }

        $this->info("Seeding {$type} collections...");

        try {
            switch ($type) {
                case 'blog':
                    FilamentCollectionsFactory::createBlog();
                    break;
                case 'cms':
                    FilamentCollectionsFactory::createCMS();
                    break;
                default:
                    $this->error("Unknown template: {$type}");

                    return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Failed to seed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("Pre-configured '{$type}' collections have been successfully created!");

        return self::SUCCESS;
    }
}

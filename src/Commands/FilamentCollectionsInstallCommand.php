<?php

namespace A21ns1g4ts\FilamentCollections\Commands;

use Illuminate\Console\Command;

class FilamentCollectionsInstallCommand extends Command
{
    protected $signature = 'filament-collections:install';

    protected $description = 'Instala o pacote Filament Collections: publica configs, migrations e roda migrate';

    public function handle()
    {
        $this->info('Publicando arquivos do Filament Collections...');

        $this->call('vendor:publish', [
            '--provider' => 'A21ns1g4ts\FilamentCollections\FilamentCollectionsServiceProvider',
            '--tag' => 'config',
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'A21ns1g4ts\FilamentCollections\FilamentCollectionsServiceProvider',
            '--tag' => 'migrations',
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'A21ns1g4ts\FilamentCollections\FilamentCollectionsServiceProvider',
            '--tag' => 'translations',
        ]);

        $this->call('migrate');

        $this->info('Filament Collections instalado com sucesso!');
    }
}

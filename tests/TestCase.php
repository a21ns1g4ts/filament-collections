<?php

namespace A21ns1g4ts\FilamentCollections\Tests;

use A21ns1g4ts\FilamentCollections\FilamentCollectionsServiceProvider;
use A21ns1g4ts\FilamentCollections\Tests\Fixtures\User;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'A21ns1g4ts\\FilamentCollections\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate:fresh', ['--drop-views' => true]);
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentCollectionsServiceProvider::class,
            \Laravel\Sanctum\SanctumServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // --- ADICIONE ESTA CONFIGURAÇÃO DO SANCTUM ---
        config()->set('auth.guards.sanctum', [
            'driver' => 'sanctum',
            'provider' => 'users', // O provider padrão para seus usuários
        ]);

        config()->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class, // Ou o caminho para o seu modelo de usuário
        ]);


    }
}

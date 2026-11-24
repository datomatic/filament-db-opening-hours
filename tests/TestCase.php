<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Tests;

use Datomatic\FilamentDatabaseOpeningHours\FilamentDatabaseOpeningHoursServiceProvider;
use Datomatic\FilamentDatabaseOpeningHours\Tests\TestSupport\AdminPanelProvider;
use Datomatic\FilamentDatabaseOpeningHours\Tests\TestSupport\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]));
    }

    protected function getPackageProviders($app): array
    {
        return [
            FilamentDatabaseOpeningHoursServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/TestSupport/database/migrations');
    }
}

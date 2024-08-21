<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;

class FilamentDatabaseOpeningHoursPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-db-opening-hours';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([OpeningHourResource::class]);
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

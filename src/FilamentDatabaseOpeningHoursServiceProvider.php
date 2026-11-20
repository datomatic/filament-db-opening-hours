<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours;

use Datomatic\FilamentDatabaseOpeningHours\Testing\TestsFilamentDatabaseOpeningHours;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentDatabaseOpeningHoursServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-db-opening-hours';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasInstallCommand(static function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('datomatic/filament-db-opening-hours');
            });
    }

    public function packageBooted(): void
    {
        Testable::mixin(new TestsFilamentDatabaseOpeningHours);
    }
}

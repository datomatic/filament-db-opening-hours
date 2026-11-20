<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Tests\TestSupport;

use Datomatic\FilamentDatabaseOpeningHours\FilamentDatabaseOpeningHoursPlugin;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->middleware([
                EncryptCookies::class,
                StartSession::class,
                ShareErrorsFromSession::class,
            ])
            ->plugin(
                FilamentDatabaseOpeningHoursPlugin::make()
                    ->navigationGroup('Settings')
                    ->navigationSort(5),
            );
    }
}

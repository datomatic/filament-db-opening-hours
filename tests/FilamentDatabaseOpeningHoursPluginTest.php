<?php

declare(strict_types=1);

use Datomatic\FilamentDatabaseOpeningHours\FilamentDatabaseOpeningHoursPlugin;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;

it('registers the opening hour resource on the panel', function () {
    expect(filament()->getPanel('admin')->getResources())->toContain(OpeningHourResource::class);
});

it('returns the plugin registered on the current panel', function () {
    expect(FilamentDatabaseOpeningHoursPlugin::current())
        ->toBeInstanceOf(FilamentDatabaseOpeningHoursPlugin::class)
        ->getNavigationGroup()->toBe('Settings');
});

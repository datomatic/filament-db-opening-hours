# Manage database opening hours in Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/datomatic/filament-db-opening-hours.svg?style=for-the-badge)](https://packagist.org/packages/datomatic/filament-db-opening-hours)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/datomatic/filament-db-opening-hours/run-tests.yml?branch=main&label=tests&color=5FE8B3&style=for-the-badge)](https://github.com/datomatic/filament-db-opening-hours/actions/workflows/run-tests.yml)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/datomatic/filament-db-opening-hours/fix-php-code-style-issues.yml?branch=main&label=code%20style&color=5FE8B3&style=for-the-badge)](https://github.com/datomatic/filament-db-opening-hours/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/datomatic/filament-db-opening-hours.svg?style=for-the-badge)](https://packagist.org/packages/datomatic/filament-db-opening-hours)

A [Filament](https://filamentphp.com) plugin to create and edit opening hours stored with [`datomatic/db-opening-hours`](https://github.com/datomatic/db-opening-hours): a weekly schedule with multiple time ranges per day, plus date exceptions (holidays, special openings).

It adds an **Opening hours** resource to your panel with one tab per weekday and a tab for the exceptions.

## Requirements

| Package | Version |
|---------|---------|
| PHP | 8.2 – 8.6 |
| Laravel | 12, 13 |
| Filament | 5 |
| `datomatic/db-opening-hours` | ^1.1 |

## Installation

Install the package via composer:

```bash
composer require datomatic/filament-db-opening-hours
```

Run the migrations of `datomatic/db-opening-hours` (they are loaded automatically):

```bash
php artisan migrate
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="filament-db-opening-hours-config"
```

Optionally publish the translations (English, Italian and Dutch are included):

```bash
php artisan vendor:publish --tag="filament-db-opening-hours-translations"
```

## Usage

Register the plugin on your panel:

```php
use Datomatic\FilamentDatabaseOpeningHours\FilamentDatabaseOpeningHoursPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(FilamentDatabaseOpeningHoursPlugin::make());
}
```

### Navigation

```php
use Filament\Support\Icons\Heroicon;

FilamentDatabaseOpeningHoursPlugin::make()
    ->navigationGroup('Settings')
    ->navigationIcon(Heroicon::OutlinedClock)
    ->navigationSort(10);
```

### Custom resource

Extend the resource to change anything (authorization, extra columns, a different model…) and register your class instead:

```php
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;

class MyOpeningHourResource extends OpeningHourResource
{
    // ...
}

FilamentDatabaseOpeningHoursPlugin::make()
    ->resource(MyOpeningHourResource::class);
```

The resource uses the model configured in `db-opening-hours.models.opening_hour`, so a model swapped through the `db-opening-hours` config is picked up automatically.

### Saving the schedule from your own code

The form is persisted by `Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule`, which you can also use directly (for example from a custom page or an action). Every write runs in a database transaction, and removing a weekday or an exception also removes its time ranges.

```php
use Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule;

$schedule = app(OpeningHourSchedule::class);

$openingHour = $schedule->create([
    'name' => 'Main shop',
    'days' => [
        'monday' => [
            'time_ranges' => [
                ['start' => '09:00', 'end' => '13:00'],
                ['start' => '14:00', 'end' => '18:00'],
            ],
        ],
    ],
    'exceptions' => [
        ['date' => '2026-12-25', 'description' => 'Christmas', 'time_ranges' => []],
    ],
]);

// Only the keys you pass are synced: this replaces the exceptions and keeps the weekly days.
$schedule->update($openingHour, ['exceptions' => []]);

$schedule->toFormState($openingHour); // the same shape, as used by the form
$schedule->delete($openingHour);      // removes days, exceptions and time ranges too
```

Notes:

- A weekday is stored only when it has at least one time range or a description.
- An exception without time ranges is kept: it marks that date as **closed**.
- Exceptions are matched by date, so the same date cannot be added twice.

## Config

```php
use Datomatic\DatabaseOpeningHours\Enums\Day;

return [
    // Show the "General" tab with a (required) name field, useful when a model holds more than one schedule
    'general_description' => false,

    // Show a description field for each weekday
    'day_description' => false,

    // Show a description field for each time range
    'time_range_description' => false,

    // Show a description field for each date exception
    'exception_description' => false,

    // The weekday shown as the first tab of the form
    'first_day_of_week' => Day::MONDAY,
];
```

## Upgrading from 0.x

Version 1 requires Filament 5 and `datomatic/db-opening-hours` ^1.1.

- The resource and its pages are no longer `final`, so they can be extended.
- Days and exceptions are saved through `OpeningHourSchedule` instead of Filament relationship components: deleting a day, an exception or a whole schedule no longer leaves orphaned time ranges behind.
- The Tailwind/PostCSS configuration files were removed: the plugin ships no custom views.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alberto Peripolli](https://github.com/trippo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

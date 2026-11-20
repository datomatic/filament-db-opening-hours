<?php

declare(strict_types=1);

use Datomatic\DatabaseOpeningHours\Enums\Day as DayEnum;
use Datomatic\DatabaseOpeningHours\Models\Day;
use Datomatic\DatabaseOpeningHours\Models\Exception;
use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\DatabaseOpeningHours\Models\TimeRange;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\CreateOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\EditOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\ListOpeningHours;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Schemas\OpeningHourForm;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Support\Icons\Heroicon;
use Livewire\Livewire;

beforeEach(function () {
    $this->undoRepeaterFake = Repeater::fake();
});

afterEach(function () {
    ($this->undoRepeaterFake)();
});

it('renders the index page', function () {
    $this->get(OpeningHourResource::getUrl('index'))->assertSuccessful();
});

it('renders the create page', function () {
    $this->get(OpeningHourResource::getUrl('create'))->assertSuccessful();
});

it('renders the edit page', function () {
    $openingHour = createOpeningHour();

    $this->get(OpeningHourResource::getUrl('edit', ['record' => $openingHour]))->assertSuccessful();
});

it('renders the view page', function () {
    $openingHour = createOpeningHour();

    $this->get(OpeningHourResource::getUrl('view', ['record' => $openingHour]))->assertSuccessful();
});

it('lists opening hours', function () {
    $openingHours = collect([
        createOpeningHour(['name' => 'Shop']),
        createOpeningHour(['name' => 'Warehouse']),
    ]);

    Livewire::test(ListOpeningHours::class)
        ->assertCanSeeTableRecords($openingHours);
});

it('creates an opening hour with its weekly schedule and exceptions', function () {
    Livewire::test(CreateOpeningHour::class)
        ->fillForm([
            'days' => [
                'monday' => [
                    'time_ranges' => [
                        ['start' => '09:00', 'end' => '13:00'],
                        ['start' => '14:00', 'end' => '18:00'],
                    ],
                ],
                'friday' => [
                    'time_ranges' => [
                        ['start' => '10:00', 'end' => '16:00'],
                    ],
                ],
            ],
            'exceptions' => [
                [
                    'date' => '2026-12-25',
                    'time_ranges' => [],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $openingHour = OpeningHour::sole();

    expect($openingHour->weeklySchedule())->toBe([
        'monday' => [
            ['start' => '09:00', 'end' => '13:00'],
            ['start' => '14:00', 'end' => '18:00'],
        ],
        'friday' => [
            ['start' => '10:00', 'end' => '16:00'],
        ],
    ])
        ->and($openingHour->exceptions()->sole()->date->toDateString())->toBe('2026-12-25')
        ->and($openingHour->openingHours()->isOpenAt(new DateTime('2026-12-25 11:00')))->toBeFalse();
});

it('requires the end of a time range to be after its start', function () {
    Livewire::test(CreateOpeningHour::class)
        ->fillForm([
            'days' => [
                'monday' => [
                    'time_ranges' => [
                        ['start' => '13:00', 'end' => '09:00'],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['days.monday.time_ranges.0.end' => 'after']);

    expect(OpeningHour::count())->toBe(0);
});

it('does not allow the same exception date twice', function () {
    Livewire::test(CreateOpeningHour::class)
        ->fillForm([
            'exceptions' => [
                ['date' => '2026-12-25', 'time_ranges' => []],
                ['date' => '2026-12-25', 'time_ranges' => []],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['exceptions.0.date']);

    expect(OpeningHour::count())->toBe(0);
});

it('requires a name when the general tab is enabled', function () {
    config()->set('filament-db-opening-hours.general_description', true);

    Livewire::test(CreateOpeningHour::class)
        ->fillForm(['name' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);

    Livewire::test(CreateOpeningHour::class)
        ->fillForm(['name' => 'Summer'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(OpeningHour::sole()->name)->toBe('Summer');
});

it('fills the edit form with the stored schedule', function () {
    $openingHour = createOpeningHour();

    $state = Livewire::test(EditOpeningHour::class, ['record' => $openingHour->getRouteKey()])
        ->get('data');

    expect(array_values($state['days']['monday']['time_ranges']))->toBe([
        ['start' => '09:00', 'end' => '13:00', 'description' => null],
        ['start' => '14:00', 'end' => '18:00', 'description' => null],
    ])
        ->and($state['days']['tuesday']['time_ranges'])->toBe([])
        ->and(array_values($state['exceptions'])[0]['date'])->toBe('2026-12-25');
});

it('updates the schedule and removes emptied days with their time ranges', function () {
    $openingHour = createOpeningHour();

    Livewire::test(EditOpeningHour::class, ['record' => $openingHour->getRouteKey()])
        ->fillForm([
            'days' => [
                'monday' => ['time_ranges' => []],
                'tuesday' => [
                    'time_ranges' => [
                        ['start' => '08:00', 'end' => '12:00'],
                    ],
                ],
            ],
            'exceptions' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($openingHour->refresh()->weeklySchedule())->toBe([
        'tuesday' => [
            ['start' => '08:00', 'end' => '12:00'],
        ],
    ])
        ->and(Day::count())->toBe(1)
        ->and(Day::sole()->day)->toBe(DayEnum::TUESDAY)
        ->and(Exception::count())->toBe(0)
        ->and(TimeRange::count())->toBe(1);
});

it('deletes an opening hour with all its days, exceptions and time ranges from the edit page', function () {
    $openingHour = createOpeningHour();

    Livewire::test(EditOpeningHour::class, ['record' => $openingHour->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(OpeningHour::count())->toBe(0)
        ->and(Day::count())->toBe(0)
        ->and(Exception::count())->toBe(0)
        ->and(TimeRange::count())->toBe(0);
});

it('deletes an opening hour from the table', function () {
    $openingHour = createOpeningHour();

    Livewire::test(ListOpeningHours::class)
        ->callTableAction(DeleteAction::class, $openingHour);

    expect(OpeningHour::count())->toBe(0)
        ->and(TimeRange::count())->toBe(0);
});

it('bulk deletes opening hours from the table', function () {
    $openingHours = collect([
        createOpeningHour(['name' => 'Shop']),
        createOpeningHour(['name' => 'Warehouse']),
    ]);

    Livewire::test(ListOpeningHours::class)
        ->callTableBulkAction(DeleteBulkAction::class, $openingHours);

    expect(OpeningHour::count())->toBe(0)
        ->and(Day::count())->toBe(0)
        ->and(TimeRange::count())->toBe(0);
});

it('reads the navigation settings from the plugin', function () {
    expect(OpeningHourResource::getNavigationGroup())->toBe('Settings')
        ->and(OpeningHourResource::getNavigationSort())->toBe(5)
        ->and(OpeningHourResource::getNavigationIcon())->toBe(Heroicon::OutlinedClock);
});

it('starts the weekday tabs from the configured first day of the week', function () {
    config()->set('filament-db-opening-hours.first_day_of_week', DayEnum::SUNDAY);

    expect(OpeningHourForm::orderedDays())->toBe([
        DayEnum::SUNDAY,
        DayEnum::MONDAY,
        DayEnum::TUESDAY,
        DayEnum::WEDNESDAY,
        DayEnum::THURSDAY,
        DayEnum::FRIDAY,
        DayEnum::SATURDAY,
    ]);
});

it('saves the descriptions when the description fields are enabled', function () {
    config()->set([
        'filament-db-opening-hours.general_description' => true,
        'filament-db-opening-hours.day_description' => true,
        'filament-db-opening-hours.time_range_description' => true,
        'filament-db-opening-hours.exception_description' => true,
    ]);

    Livewire::test(CreateOpeningHour::class)
        ->fillForm([
            'name' => 'Summer',
            'days' => [
                'monday' => [
                    'description' => 'Short day',
                    'time_ranges' => [
                        ['start' => '09:00', 'end' => '12:00', 'description' => 'Morning'],
                    ],
                ],
            ],
            'exceptions' => [
                ['date' => '2026-08-15', 'description' => 'Ferragosto', 'time_ranges' => []],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $openingHour = OpeningHour::sole();
    $monday = $openingHour->monday()->sole();

    expect($openingHour->name)->toBe('Summer')
        ->and($monday->description)->toBe('Short day')
        ->and($monday->timeRanges->sole()->description)->toBe('Morning')
        ->and($openingHour->exceptions()->sole()->description)->toBe('Ferragosto');

    $this->get(OpeningHourResource::getUrl('edit', ['record' => $openingHour]))
        ->assertSuccessful()
        ->assertSee('Short day')
        ->assertSee('Ferragosto');
});

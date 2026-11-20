<?php

declare(strict_types=1);

use Datomatic\DatabaseOpeningHours\Enums\Day as DayEnum;
use Datomatic\DatabaseOpeningHours\Models\Exception;
use Datomatic\DatabaseOpeningHours\Models\TimeRange;
use Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule;

it('exposes every weekday and exception as form state', function () {
    $state = app(OpeningHourSchedule::class)->toFormState(createOpeningHour());

    expect(array_keys($state['days']))->toBe(array_column(DayEnum::cases(), 'value'))
        ->and($state['days']['monday']['time_ranges'])->toHaveCount(2)
        ->and($state['days']['sunday'])->toBe(['description' => null, 'time_ranges' => []])
        ->and($state['exceptions'])->toBe([
            [
                'date' => '2026-12-25',
                'description' => null,
                'time_ranges' => [
                    ['start' => '10:00', 'end' => '12:00', 'description' => null],
                ],
            ],
        ]);
});

it('keeps a weekday that only has a description', function () {
    $openingHour = createOpeningHour([
        'days' => [
            'sunday' => ['description' => 'Closed for maintenance', 'time_ranges' => []],
        ],
    ]);

    $sunday = $openingHour->sunday()->sole();

    expect($sunday->description)->toBe('Closed for maintenance')
        ->and($sunday->timeRanges)->toBeEmpty()
        ->and($openingHour->monday()->exists())->toBeFalse();
});

it('ignores incomplete time ranges', function () {
    $openingHour = createOpeningHour([
        'days' => [
            'monday' => [
                'time_ranges' => [
                    ['start' => '09:00', 'end' => null],
                    ['start' => '', 'end' => '12:00'],
                    ['start' => '14:00', 'end' => '18:00'],
                ],
            ],
        ],
    ]);

    expect($openingHour->weeklySchedule())->toBe([
        'monday' => [
            ['start' => '14:00', 'end' => '18:00'],
        ],
    ]);
});

it('keeps exceptions without time ranges as closed dates', function () {
    $openingHour = createOpeningHour([
        'exceptions' => [
            ['date' => '2026-01-01', 'description' => 'New year', 'time_ranges' => []],
        ],
    ]);

    expect($openingHour->exceptions()->sole()->description)->toBe('New year')
        ->and($openingHour->openingHours()->isClosedOn('2026-01-01'))->toBeTrue();
});

it('updates exceptions by date and removes the dropped ones with their time ranges', function () {
    $openingHour = createOpeningHour([
        'exceptions' => [
            ['date' => '2026-12-25', 'time_ranges' => [['start' => '10:00', 'end' => '12:00']]],
            ['date' => '2026-12-26', 'time_ranges' => [['start' => '10:00', 'end' => '12:00']]],
        ],
    ]);
    $christmas = $openingHour->exceptions()->whereDate('date', '2026-12-25')->sole();

    app(OpeningHourSchedule::class)->update($openingHour, [
        'exceptions' => [
            ['date' => '2026-12-25', 'description' => 'Christmas', 'time_ranges' => [['start' => '09:00', 'end' => '11:00']]],
        ],
    ]);

    expect(Exception::sole()->id)->toBe($christmas->id)
        ->and(Exception::sole()->description)->toBe('Christmas')
        ->and(TimeRange::where('time_rangeable_type', 'exception')->count())->toBe(1);
});

it('only syncs the parts of the schedule present in the state', function () {
    $openingHour = createOpeningHour(['name' => 'Shop']);

    app(OpeningHourSchedule::class)->update($openingHour, ['days' => []]);

    expect($openingHour->refresh()->name)->toBe('Shop')
        ->and($openingHour->days()->count())->toBe(0)
        ->and($openingHour->exceptions()->count())->toBe(1);
});

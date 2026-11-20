<?php

declare(strict_types=1);

use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule;
use Datomatic\FilamentDatabaseOpeningHours\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * @param  array<string, mixed>  $state
 */
function createOpeningHour(array $state = []): OpeningHour
{
    return app(OpeningHourSchedule::class)->create([
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
            [
                'date' => '2026-12-25',
                'time_ranges' => [
                    ['start' => '10:00', 'end' => '12:00'],
                ],
            ],
        ],
        ...$state,
    ]);
}

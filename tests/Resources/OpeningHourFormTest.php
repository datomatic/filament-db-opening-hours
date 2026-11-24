<?php

declare(strict_types=1);

use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Tests\TestSupport\Livewire\EmbeddedScheduleForm;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;

beforeEach(function () {
    $this->undoRepeaterFake = Repeater::fake();
});

afterEach(function () {
    ($this->undoRepeaterFake)();
});

it('fills the embedded tabs with the stored schedule under the parent state path', function () {
    $openingHour = createOpeningHour();

    $state = Livewire::test(EmbeddedScheduleForm::class, ['openingHour' => $openingHour])
        ->assertSee(['Monday', 'Sunday', 'Exceptions'])
        ->get('data.schedule');

    expect(array_values($state['days']['monday']['time_ranges']))->toBe([
        ['start' => '09:00', 'end' => '13:00', 'description' => null],
        ['start' => '14:00', 'end' => '18:00', 'description' => null],
    ])
        ->and(array_values($state['exceptions'])[0]['date'])->toBe('2026-12-25');
});

it('saves the embedded tabs with the schedule service', function () {
    $openingHour = createOpeningHour();

    Livewire::test(EmbeddedScheduleForm::class, ['openingHour' => $openingHour])
        ->fillForm([
            'schedule' => [
                'days' => [
                    'monday' => ['time_ranges' => []],
                    'tuesday' => ['time_ranges' => [['start' => '08:00', 'end' => '12:00']]],
                ],
                'exceptions' => [['date' => '2026-08-15', 'time_ranges' => []]],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $openingHour = OpeningHour::sole();

    expect($openingHour->days()->get()->map(static fn ($day): string => $day->day->value)->all())->toBe(['tuesday'])
        ->and($openingHour->exceptions()->get()->map(static fn ($exception): string => $exception->date->format('Y-m-d'))->all())->toBe(['2026-08-15']);
});

it('validates the embedded time ranges', function () {
    Livewire::test(EmbeddedScheduleForm::class, ['openingHour' => createOpeningHour()])
        ->fillForm([
            'schedule' => [
                'days' => [
                    'monday' => ['time_ranges' => [['start' => '12:00', 'end' => '09:00']]],
                ],
            ],
        ])
        ->call('save')
        ->assertHasFormErrors(['schedule.days.monday.time_ranges.0.end' => 'after']);
});

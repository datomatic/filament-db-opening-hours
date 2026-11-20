<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Support;

use Carbon\Carbon;
use Datomatic\DatabaseOpeningHours\Enums\Day as DayEnum;
use Datomatic\DatabaseOpeningHours\Models\Day;
use Datomatic\DatabaseOpeningHours\Models\Exception;
use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\DatabaseOpeningHours\Models\TimeRange;
use Datomatic\DatabaseOpeningHours\Support\Models;

/**
 * Maps an opening hour to the resource form state and persists it back.
 *
 * Days and exceptions are not saved through Filament relationship components:
 * time ranges are polymorphic (no foreign key), so removing a day or an
 * exception must also remove its ranges, which Filament would leave orphaned.
 *
 * @phpstan-type TimeRangeState array{start: ?string, end: ?string, description?: ?string}
 * @phpstan-type DayState array{description?: ?string, time_ranges?: array<array-key, TimeRangeState>}
 * @phpstan-type ExceptionState array{date: ?string, description?: ?string, time_ranges?: array<array-key, TimeRangeState>}
 * @phpstan-type ScheduleState array{
 *     name?: ?string,
 *     days?: array<string, DayState>,
 *     exceptions?: array<array-key, ExceptionState>
 * }
 */
class OpeningHourSchedule
{
    /**
     * @return array{
     *     days: array<string, array{description: ?string, time_ranges: list<array{start: string, end: string, description: ?string}>}>,
     *     exceptions: list<array{date: string, description: ?string, time_ranges: list<array{start: string, end: string, description: ?string}>}>
     * }
     */
    public function toFormState(OpeningHour $openingHour): array
    {
        $days = $openingHour->days()
            ->get()
            ->keyBy(static fn (Day $day): string => $day->day->value);

        $daysState = [];

        foreach (DayEnum::cases() as $dayEnum) {
            $day = $days->get($dayEnum->value);

            $daysState[$dayEnum->value] = [
                'description' => $day?->description,
                'time_ranges' => $day ? $this->timeRangesState($day) : [],
            ];
        }

        $exceptionsState = $openingHour->exceptions()
            ->get()
            ->map(fn (Exception $exception): array => [
                'date' => $exception->date->format('Y-m-d'),
                'description' => $exception->description,
                'time_ranges' => $this->timeRangesState($exception),
            ])
            ->values()
            ->all();

        return [
            'days' => $daysState,
            'exceptions' => $exceptionsState,
        ];
    }

    /**
     * @param  ScheduleState  $state
     */
    public function create(array $state): OpeningHour
    {
        $openingHourClass = Models::openingHour();

        return (new $openingHourClass)->getConnection()->transaction(function () use ($openingHourClass, $state): OpeningHour {
            $openingHour = $openingHourClass::create(['name' => $state['name'] ?? null]);

            $this->sync($openingHour, $state);

            return $openingHour;
        });
    }

    /**
     * @param  ScheduleState  $state
     */
    public function update(OpeningHour $openingHour, array $state): OpeningHour
    {
        $openingHour->getConnection()->transaction(function () use ($openingHour, $state): void {
            if (array_key_exists('name', $state)) {
                $openingHour->update(['name' => $state['name']]);
            }

            $this->sync($openingHour, $state);
        });

        return $openingHour;
    }

    public function delete(OpeningHour $openingHour): bool
    {
        return $openingHour->getConnection()->transaction(function () use ($openingHour): bool {
            $openingHour->days()->get()->each(fn (Day $day) => $this->deleteWithTimeRanges($day));
            $openingHour->exceptions()->get()->each(fn (Exception $exception) => $this->deleteWithTimeRanges($exception));

            return (bool) $openingHour->delete();
        });
    }

    /**
     * Only the parts present in the state are synced, so a partial update
     * (e.g. just the exceptions) leaves the rest of the schedule untouched.
     *
     * @param  ScheduleState  $state
     */
    private function sync(OpeningHour $openingHour, array $state): void
    {
        if (array_key_exists('days', $state)) {
            $this->syncDays($openingHour, $state['days']);
        }

        if (array_key_exists('exceptions', $state)) {
            $this->syncExceptions($openingHour, $state['exceptions']);
        }

        $openingHour->unsetRelation('days');
        $openingHour->unsetRelation('exceptions');
    }

    /**
     * A weekday is stored only when it has at least one time range or a
     * description; otherwise it is removed together with its ranges.
     *
     * @param  array<string, DayState>  $daysState
     */
    private function syncDays(OpeningHour $openingHour, array $daysState): void
    {
        foreach (DayEnum::cases() as $dayEnum) {
            $dayState = $daysState[$dayEnum->value] ?? [];
            $timeRanges = $this->filledTimeRanges($dayState['time_ranges'] ?? []);
            $description = $this->nullIfBlank($dayState['description'] ?? null);

            $day = $openingHour->days()
                ->where('day', $dayEnum)
                ->first();

            if ($timeRanges === [] && $description === null) {
                if ($day) {
                    $this->deleteWithTimeRanges($day);
                }

                continue;
            }

            $day ??= $openingHour->days()->make(['day' => $dayEnum]);
            $day->description = $description;
            $day->save();

            $this->replaceTimeRanges($day, $timeRanges);
        }
    }

    /**
     * Exceptions are matched by date. An exception without time ranges is kept:
     * it marks the date as closed.
     *
     * @param  array<array-key, ExceptionState>  $exceptionsState
     */
    private function syncExceptions(OpeningHour $openingHour, array $exceptionsState): void
    {
        $existingExceptions = $openingHour->exceptions()
            ->get()
            ->keyBy(static fn (Exception $exception): string => $exception->date->format('Y-m-d'));

        $keptDates = [];

        foreach ($exceptionsState as $exceptionState) {
            if (blank($exceptionState['date'] ?? null)) {
                continue;
            }

            $date = Carbon::parse($exceptionState['date'])->toDateString();
            $keptDates[] = $date;

            $exception = $existingExceptions->get($date) ?? $openingHour->exceptions()->make(['date' => $date]);
            $exception->description = $this->nullIfBlank($exceptionState['description'] ?? null);
            $exception->save();

            $this->replaceTimeRanges($exception, $this->filledTimeRanges($exceptionState['time_ranges'] ?? []));
        }

        $existingExceptions
            ->reject(static fn (Exception $exception): bool => in_array($exception->date->format('Y-m-d'), $keptDates, true))
            ->each(fn (Exception $exception) => $this->deleteWithTimeRanges($exception));
    }

    /**
     * @param  list<array{start: string, end: string, description: ?string}>  $timeRanges
     */
    private function replaceTimeRanges(Day | Exception $model, array $timeRanges): void
    {
        $model->timeRanges()->delete();

        foreach ($timeRanges as $timeRange) {
            $model->timeRanges()->create($timeRange);
        }

        $model->unsetRelation('timeRanges');
    }

    private function deleteWithTimeRanges(Day | Exception $model): void
    {
        $model->timeRanges()->delete();
        $model->delete();
    }

    /**
     * @param  array<array-key, TimeRangeState>  $timeRanges
     * @return list<array{start: string, end: string, description: ?string}>
     */
    private function filledTimeRanges(array $timeRanges): array
    {
        $filled = [];

        foreach ($timeRanges as $timeRange) {
            if (blank($timeRange['start'] ?? null)) {
                continue;
            }

            if (blank($timeRange['end'] ?? null)) {
                continue;
            }

            $filled[] = [
                'start' => (string) $timeRange['start'],
                'end' => (string) $timeRange['end'],
                'description' => $this->nullIfBlank($timeRange['description'] ?? null),
            ];
        }

        return $filled;
    }

    /**
     * @return list<array{start: string, end: string, description: ?string}>
     */
    private function timeRangesState(Day | Exception $model): array
    {
        $timeRanges = [];

        /** @var TimeRange $timeRange */
        foreach ($model->timeRanges()->get() as $timeRange) {
            $timeRanges[] = [
                'start' => $timeRange->start->format('H:i'),
                'end' => $timeRange->end->format('H:i'),
                'description' => $timeRange->description,
            ];
        }

        return $timeRanges;
    }

    private function nullIfBlank(?string $value): ?string
    {
        return blank($value) ? null : $value;
    }
}

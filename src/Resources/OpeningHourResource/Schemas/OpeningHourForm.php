<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Schemas;

use Datomatic\DatabaseOpeningHours\Enums\Day;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class OpeningHourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('opening-hours')
                    ->id('opening-hours')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        self::generalTab(),
                        ...array_map(self::dayTab(...), self::orderedDays()),
                        self::exceptionsTab(),
                    ]),
            ])
            ->columns(1);
    }

    /**
     * @return list<Day>
     */
    public static function orderedDays(): array
    {
        $days = Day::cases();
        $firstDay = config('filament-db-opening-hours.first_day_of_week', Day::MONDAY);

        if (! $firstDay instanceof Day) {
            $firstDay = Day::tryFrom((string) $firstDay) ?? Day::MONDAY;
        }

        $offset = (int) array_search($firstDay, $days, true);

        return [
            ...array_slice($days, $offset),
            ...array_slice($days, 0, $offset),
        ];
    }

    private static function generalTab(): Tab
    {
        return Tab::make('general')
            ->id('general')
            ->label(__('filament-db-opening-hours::labels.general'))
            ->icon(Heroicon::OutlinedBuildingStorefront)
            ->visible((bool) config('filament-db-opening-hours.general_description'))
            ->schema([
                TextInput::make('name')
                    ->label(__('filament-db-opening-hours::labels.name'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    private static function dayTab(Day $day): Tab
    {
        return Tab::make($day->value)
            ->id($day->value)
            ->label($day->label())
            ->icon(Heroicon::OutlinedCalendarDays)
            ->schema([
                Group::make()
                    ->statePath("days.{$day->value}")
                    ->schema([
                        self::descriptionInput()
                            ->visible((bool) config('filament-db-opening-hours.day_description')),
                        self::timeRangesRepeater(),
                    ]),
            ]);
    }

    private static function exceptionsTab(): Tab
    {
        return Tab::make('exceptions')
            ->id('exceptions')
            ->label(__('filament-db-opening-hours::labels.exceptions'))
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->schema([
                Repeater::make('exceptions')
                    ->label(__('filament-db-opening-hours::labels.exceptions'))
                    ->addActionLabel(__('filament-db-opening-hours::labels.add_exception'))
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(static fn (array $state): ?string => filled($state['date'] ?? null)
                        ? Carbon::parse($state['date'])->translatedFormat('j F Y')
                        : null)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('date')
                                    ->label(__('filament-db-opening-hours::labels.date'))
                                    ->required()
                                    ->distinct(),
                                self::descriptionInput()
                                    ->visible((bool) config('filament-db-opening-hours.exception_description')),
                            ]),
                        self::timeRangesRepeater(),
                    ]),
            ]);
    }

    private static function timeRangesRepeater(): Repeater
    {
        return Repeater::make('time_ranges')
            ->label(__('filament-db-opening-hours::labels.time_ranges'))
            ->addActionLabel(__('filament-db-opening-hours::labels.add_time_range'))
            ->defaultItems(0)
            ->reorderable(false)
            ->grid(2)
            ->itemLabel(static fn (array $state): ?string => filled($state['start'] ?? null) && filled($state['end'] ?? null)
                ? substr((string) $state['start'], 0, 5) . ' - ' . substr((string) $state['end'], 0, 5)
                : null)
            ->schema([
                Grid::make(2)
                    ->schema([
                        TimePicker::make('start')
                            ->label(__('filament-db-opening-hours::labels.start'))
                            ->seconds(false)
                            ->required(),
                        TimePicker::make('end')
                            ->label(__('filament-db-opening-hours::labels.end'))
                            ->seconds(false)
                            ->required()
                            ->after('start'),
                    ]),
                self::descriptionInput()
                    ->visible((bool) config('filament-db-opening-hours.time_range_description')),
            ]);
    }

    private static function descriptionInput(): TextInput
    {
        return TextInput::make('description')
            ->label(__('filament-db-opening-hours::labels.description'))
            ->maxLength(255);
    }
}

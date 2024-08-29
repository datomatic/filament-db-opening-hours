<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources;

use Datomatic\DatabaseOpeningHours\Enums\Day;
use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\CreateOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\EditOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\ListOpeningHours;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\ViewOpeningHour;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class OpeningHourResource extends Resource
{
    protected static ?string $model = OpeningHour::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return trans('filament-db-opening-hours::labels.opening_hour');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('filament-db-opening-hours::labels.opening_hours');
    }

    public static function form(Form $form): Form
    {
        $cases = Day::cases();
        $offset = array_search(config('filament-db-opening-hours.first_day_of_week'), $cases);
        $cases = array_merge(array_slice($cases, $offset), array_slice($cases, 0, $offset));

        return $form
            ->schema([
                Tabs::make('opening-hours')
                    ->id('opening-hours')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('general')
                            ->id('general')
                            ->label('filament-db-opening-hours::labels.general')
                            ->translateLabel()
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                TextInput::make('name')
                                    ->label('filament-db-opening-hours::labels.name')
                                    ->translateLabel()
                                    ->required()
                                    ->minLength(1)
                                    ->maxLength(255),
                            ])->visible(config('filament-db-opening-hours.general_description')),
                        ...array_map(fn ($day) => self::dayTab($day), $cases),
                        Tab::make('exceptions')
                            ->label('filament-db-opening-hours::labels.exceptions')
                            ->translateLabel()
                            ->icon('heroicon-o-exclamation-triangle')
                            ->schema([
                                Repeater::make('exception')
                                    ->label('filament-db-opening-hours::labels.exception')
                                    ->translateLabel()
                                    ->minItems(0)
                                    ->defaultItems(0)
                                    ->addActionLabel(trans('filament-db-opening-hours::labels.add_exception'))
                                    ->relationship('exceptions')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('description')
                                                ->label('filament-db-opening-hours::labels.description')
                                                ->translateLabel()
                                                ->minLength(1)
                                                ->maxLength(255)
                                                ->visible(config('filament-db-opening-hours.exception_description')),
                                            DatePicker::make('date')
                                                ->label('filament-db-opening-hours::labels.date')
                                                ->translateLabel()
                                                ->required(),
                                        ])->columns(2),
                                        self::timeRangeRepeater(),
                                    ]),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    private static function dayTab(Day $day): Tab
    {
        return Tab::make($day->label())
            ->label($day->label())
            ->id($day->value)
            ->translateLabel()
            ->icon('heroicon-o-calendar-days')
            ->schema([
                Grid::make()
                    ->relationship($day->value)
                    ->mutateRelationshipDataBeforeCreateUsing(static fn () => [
                        'day' => $day,
                    ])
                    ->columns(1)
                    ->schema([
                        TextInput::make('description')
                            ->label('filament-db-opening-hours::labels.description')
                            ->translateLabel()
                            ->minLength(1)
                            ->maxLength(255)
                            ->visible(config('filament-db-opening-hours.day_description')),
                        self::timeRangeRepeater(),
                    ]),
            ]);
    }

    private static function timeRangeRepeater(): Repeater
    {
        return Repeater::make('timeRanges')
            ->label('filament-db-opening-hours::labels.time_ranges')
            ->addActionLabel(trans('filament-db-opening-hours::labels.add_time_range'))
            ->translateLabel()
            ->collapsible()
            ->collapsed(fn ($state) => empty($state['id']))
            ->itemLabel(fn (array $state): ?string => $state['start'] . ' - ' . $state['end'])
            ->relationship()
            ->reorderable(true)
            ->defaultItems(0)
            ->minItems(0)
            ->grid(2)
            ->schema([
                TextInput::make('description')
                    ->label('filament-db-opening-hours::labels.description')
                    ->translateLabel()
                    ->minLength(1)
                    ->maxLength(255)
                    ->visible(config('filament-db-opening-hours.time_range_description')),
                Grid::make()
                    ->schema([
                        TimePicker::make('start')
                            ->label('filament-db-opening-hours::labels.start')
                            ->translateLabel()
                            ->seconds(false)
                            ->required(),
                        TimePicker::make('end')
                            ->label('filament-db-opening-hours::labels.end')
                            ->translateLabel()
                            ->seconds(false)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOpeningHours::route('/'),
            'create' => CreateOpeningHour::route('/create'),
            'view' => ViewOpeningHour::route('/{record}'),
            'edit' => EditOpeningHour::route('/{record}/edit'),
        ];
    }
}

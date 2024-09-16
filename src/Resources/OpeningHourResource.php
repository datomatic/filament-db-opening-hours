<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources;

use Awcodes\FilamentTableRepeater\Components\TableRepeater;
use Datomatic\DatabaseOpeningHours\Enums\Day;
use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\CreateOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\EditOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\ListOpeningHours;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\ViewOpeningHour;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

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
                                    ->columns(2)
                                    ->collapsed()
                                    ->hiddenLabel()
                                    // TODO: maybe description as well?
                                    ->itemLabel(fn (array $state) => $state['date'])

                                    ->label('filament-db-opening-hours::labels.exception')
                                    ->translateLabel()
                                    ->minItems(0)
                                    ->defaultItems(0)
                                    ->addActionLabel(trans('filament-db-opening-hours::labels.add_exception'))
                                    ->relationship('exceptions')
                                    ->schema([
                                        TextInput::make('description')
                                            ->hiddenLabel()

                                            ->label('filament-db-opening-hours::labels.description')
                                            ->translateLabel()
                                            ->minLength(1)
                                            ->maxLength(255)
                                            ->visible(config('filament-db-opening-hours.exception_description')),
                                        DatePicker::make('date')
                                            ->hiddenLabel()

                                            ->label('filament-db-opening-hours::labels.date')
                                            ->translateLabel()
                                            ->required(),
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
        return TableRepeater::make('timeRanges')
            ->emptyLabel(' ')
            ->hiddenLabel()
            ->addAction(function (Action $action) {
                return $action
                    ->form(static::timeRangeForm('add'))
                    ->modalWidth(MaxWidth::FourExtraLarge)
                    ->action(function ($data, TableRepeater $component) {
                        $newUuid = $component->generateUuid();
                        $items = $component->getState();
                        $items[$newUuid] = [];
                        $component->state($items);
                        $component
                            ->getChildComponentContainer($newUuid)
                            ->fill($data);

                        $component->callAfterStateUpdated();
                    });
            })
            ->cloneable()
            ->cloneAction(function (Action $action) {
                return $action
                    ->icon('heroicon-m-pencil-square')
                    ->color('primary')

                    ->form(static::timeRangeForm())
                    ->action(function (
                        array $arguments,
                        Repeater $component,
                        array $data
                    ): void {
                        $i = $arguments['item'];

                        $items = $component->getState();
                        $items[$i] = $data;
                        $component->state($items);

                        $component
                            ->getChildComponentContainer($i)
                            ->fill($data);
                        $component->dehydrateState($items);

                        $component->collapsed(
                            false,
                            shouldMakeComponentCollapsible: false
                        );

                        $component->callAfterStateUpdated();
                    })
                    ->modalWidth(MaxWidth::FourExtraLarge)

                    ->fillForm(function (array $arguments, $state) {
                        $i = $state[$arguments['item']];

                        return $i;
                    });
            })

            ->label('filament-db-opening-hours::labels.time_ranges')
            ->addActionLabel(trans('filament-db-opening-hours::labels.add_time_range'))
            ->translateLabel()

            ->relationship()
            ->defaultItems(0)
            ->minItems(0)
            ->schema([
                Hidden::make('start')->dehydrated(true),
                Hidden::make('end')->dehydrated(true),
                Placeholder::make('item')->content(fn ($record, $get) => Carbon::parse($get('start'))
                    ->timezone(config('app.timezone'))
                    ->format('H:i') . ' - ' . Carbon::parse($get('end'))
                    ->timezone(config('app.timezone'))
                    ->format('H:i'))->hiddenLabel(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
        ]);
    }

    public static function timeRangeForm(): array
    {
        return [
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
        ];
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

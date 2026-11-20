<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources;

use BackedEnum;
use Datomatic\DatabaseOpeningHours\Support\Models;
use Datomatic\FilamentDatabaseOpeningHours\FilamentDatabaseOpeningHoursPlugin;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\CreateOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\EditOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\ListOpeningHours;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages\ViewOpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Schemas\OpeningHourForm;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Tables\OpeningHoursTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class OpeningHourResource extends Resource
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModel(): string
    {
        return Models::openingHour();
    }

    public static function getModelLabel(): string
    {
        return __('filament-db-opening-hours::labels.opening_hour');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-db-opening-hours::labels.opening_hours');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return FilamentDatabaseOpeningHoursPlugin::current()?->getNavigationGroup() ?? parent::getNavigationGroup();
    }

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return FilamentDatabaseOpeningHoursPlugin::current()?->getNavigationIcon() ?? parent::getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentDatabaseOpeningHoursPlugin::current()?->getNavigationSort() ?? parent::getNavigationSort();
    }

    public static function form(Schema $schema): Schema
    {
        return OpeningHourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpeningHoursTable::configure($table);
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

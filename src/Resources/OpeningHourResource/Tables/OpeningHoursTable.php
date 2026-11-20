<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Tables;

use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class OpeningHoursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-db-opening-hours::labels.name'))
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('openable_type')
                    ->label(__('filament-db-opening-hours::labels.openable'))
                    ->formatStateUsing(static fn (OpeningHour $record): string => class_basename($record->openable_type) . " #{$record->openable_id}")
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('days_count')
                    ->label(__('filament-db-opening-hours::labels.days'))
                    ->counts('days')
                    ->sortable(),
                TextColumn::make('exceptions_count')
                    ->label(__('filament-db-opening-hours::labels.exceptions'))
                    ->counts('exceptions')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('filament-db-opening-hours::labels.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->using(static fn (OpeningHour $record): bool => app(OpeningHourSchedule::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(static function (Collection $records): void {
                            $records->each(static fn (OpeningHour $record): bool => app(OpeningHourSchedule::class)->delete($record));
                        }),
                ]),
            ]);
    }
}

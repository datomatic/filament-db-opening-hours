<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewOpeningHour extends ViewRecord
{
    protected static string $resource = OpeningHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

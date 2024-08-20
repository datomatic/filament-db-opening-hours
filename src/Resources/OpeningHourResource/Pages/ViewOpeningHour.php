<?php

declare(strict_types=1);

namespace Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource;
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

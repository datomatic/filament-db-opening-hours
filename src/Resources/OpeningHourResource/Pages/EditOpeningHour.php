<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;

final class EditOpeningHour extends EditRecord
{
    protected static string $resource = OpeningHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

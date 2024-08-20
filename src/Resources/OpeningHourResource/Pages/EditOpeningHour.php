<?php

declare(strict_types=1);

namespace Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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

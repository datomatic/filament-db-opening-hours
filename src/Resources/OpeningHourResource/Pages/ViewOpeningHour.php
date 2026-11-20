<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;
use Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOpeningHour extends ViewRecord
{
    protected static string $resource = OpeningHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var OpeningHour $record */
        $record = $this->getRecord();

        return [
            ...$data,
            ...app(OpeningHourSchedule::class)->toFormState($record),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;
use Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOpeningHour extends EditRecord
{
    protected static string $resource = OpeningHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->using(static fn (OpeningHour $record): bool => app(OpeningHourSchedule::class)->delete($record)),
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var OpeningHour $record */
        return app(OpeningHourSchedule::class)->update($record, $data);
    }
}

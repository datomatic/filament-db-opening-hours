<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;
use Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOpeningHour extends CreateRecord
{
    protected static string $resource = OpeningHourResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(OpeningHourSchedule::class)->create($data);
    }
}

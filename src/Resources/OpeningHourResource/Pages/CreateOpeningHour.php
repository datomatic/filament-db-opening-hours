<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateOpeningHour extends CreateRecord
{
    protected static string $resource = OpeningHourResource::class;
}

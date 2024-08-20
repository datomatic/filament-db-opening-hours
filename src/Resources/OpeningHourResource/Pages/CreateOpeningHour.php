<?php

declare(strict_types=1);

namespace Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateOpeningHour extends CreateRecord
{
    protected static string $resource = OpeningHourResource::class;
}

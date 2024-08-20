<?php

declare(strict_types=1);

namespace Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource;

final class CreateOpeningHour extends CreateRecord
{
    protected static string $resource = OpeningHourResource::class;
}

<?php

declare(strict_types=1);

namespace Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource\Pages;

use Datomatic\DatabaseOpeningHours\Resources\OpeningHourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListOpeningHours extends ListRecords
{
    protected static string $resource = OpeningHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

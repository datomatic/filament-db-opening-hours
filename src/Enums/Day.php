<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Enums;

use Filament\Support\Contracts\HasLabel;

enum Day: string implements HasLabel
{
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function label(): string
    {
        return trans("filament-db-opening-hours::days.$this->value");
    }
}

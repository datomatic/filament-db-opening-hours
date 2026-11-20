<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours;

use BackedEnum;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
use UnitEnum;

class FilamentDatabaseOpeningHoursPlugin implements Plugin
{
    public const Id = 'filament-db-opening-hours';

    /** @var class-string<OpeningHourResource> */
    protected string $resource = OpeningHourResource::class;

    protected string | UnitEnum | null $navigationGroup = null;

    protected string | BackedEnum | null $navigationIcon = null;

    protected ?int $navigationSort = null;

    public function getId(): string
    {
        return self::Id;
    }

    public function register(Panel $panel): void
    {
        $panel->resources([$this->getResource()]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(self::Id);

        return $plugin;
    }

    /**
     * The plugin registered on the current panel, or null when the resource is
     * used without it (for example registered manually on a panel).
     */
    public static function current(): ?static
    {
        $panel = filament()->getCurrentOrDefaultPanel();

        if (! $panel?->hasPlugin(self::Id)) {
            return null;
        }

        /** @var static $plugin */
        $plugin = $panel->getPlugin(self::Id);

        return $plugin;
    }

    /**
     * @param  class-string<OpeningHourResource>  $resource
     */
    public function resource(string $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return class-string<OpeningHourResource>
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    public function navigationGroup(string | UnitEnum | null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): string | UnitEnum | null
    {
        return $this->navigationGroup;
    }

    public function navigationIcon(string | BackedEnum | null $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): string | BackedEnum | null
    {
        return $this->navigationIcon;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }
}

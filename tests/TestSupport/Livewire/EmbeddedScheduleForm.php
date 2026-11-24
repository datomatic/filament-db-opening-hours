<?php

declare(strict_types=1);

namespace Datomatic\FilamentDatabaseOpeningHours\Tests\TestSupport\Livewire;

use Datomatic\DatabaseOpeningHours\Models\OpeningHour;
use Datomatic\FilamentDatabaseOpeningHours\Resources\OpeningHourResource\Schemas\OpeningHourForm;
use Datomatic\FilamentDatabaseOpeningHours\Support\OpeningHourSchedule;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

/**
 * A form of another model that embeds the schedule tabs in a section.
 */
class EmbeddedScheduleForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public OpeningHour $openingHour;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(OpeningHour $openingHour): void
    {
        $this->openingHour = $openingHour;

        $this->form->fill([
            'title' => 'Company',
            'schedule' => app(OpeningHourSchedule::class)->toFormState($openingHour),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title'),
                Section::make('Opening hours')
                    ->statePath('schedule')
                    ->schema([OpeningHourForm::scheduleTabs('schedule')]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        app(OpeningHourSchedule::class)->update($this->openingHour, $this->form->getState()['schedule']);
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>{{ $this->form }}</div>
        BLADE;
    }
}

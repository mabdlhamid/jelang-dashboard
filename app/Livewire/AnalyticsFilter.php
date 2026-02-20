<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

class AnalyticsFilter extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $filterType = 'date';
    public ?string $selectedDate = null;
    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('filterType')
                ->label('Filter By')
                ->options([
                    'date' => 'Specific Date',
                    'month' => 'Month',
                    'year' => 'Year',
                ])
                ->reactive()
                ->default('date'),

            DatePicker::make('selectedDate')
                ->label('Select Date')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->visible(fn ($get) => $get('filterType') === 'date')
                ->reactive(),

            Select::make('selectedMonth')
                ->label('Select Month')
                ->options([
                    1 => 'January', 2 => 'February', 3 => 'March',
                    4 => 'April', 5 => 'May', 6 => 'June',
                    7 => 'July', 8 => 'August', 9 => 'September',
                    10 => 'October', 11 => 'November', 12 => 'December',
                ])
                ->default(now()->month)
                ->visible(fn ($get) => $get('filterType') === 'month')
                ->reactive(),

            Select::make('selectedYear')
                ->label('Select Year')
                ->options(function () {
                    $years = [];
                    for ($y = now()->year - 5; $y <= now()->year; $y++) {
                        $years[$y] = $y;
                    }
                    return $years;
                })
                ->default(now()->year)
                ->visible(fn ($get) => in_array($get('filterType'), ['month', 'year']))
                ->reactive(),
        ])->columns(3);
    }

    public function updated(): void
    {
        $this->dispatch('filter-updated', [
            'type' => $this->filterType,
            'date' => $this->selectedDate,
            'month' => $this->selectedMonth,
            'year' => $this->selectedYear,
        ]);
    }

    public function render()
    {
        return view('livewire.analytics-filter');
    }
}
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Models\Sale;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Select Report Period')
                    ->description('Choose the date range for your sales report')
                    ->schema([
                       DatePicker::make('start_date')
    ->label('Start Date')
    ->required()
    ->default(now()->startOfMonth())
    ->native(false)
    ->displayFormat('d/m/Y'),

DatePicker::make('end_date')
    ->label('End Date')
    ->required()
    ->default(now()->endOfMonth())
    ->native(false)
    ->displayFormat('d/m/Y')
    ->afterOrEqual('start_date'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function exportPdf()
    {
        $data = $this->form->getState();

        $sales = Sale::with('menu')
            ->whereBetween('transaction_date', [$data['start_date'], $data['end_date']])
            ->where('payment_status', 'paid')
            ->orderBy('transaction_date', 'desc')
            ->get();

        if ($sales->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('No Data Found')
                ->body('No sales transactions found for the selected period.')
                ->send();
            return;
        }

        $totalRevenue = $sales->sum('total_price');
        $totalTransactions = $sales->count();
        $totalItems = $sales->sum('quantity');

        $pdf = Pdf::loadView('reports.sales-pdf', [
            'sales' => $sales,
            'startDate' => $data['start_date'],
            'endDate' => $data['end_date'],
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'totalItems' => $totalItems,
        ]);

        Notification::make()
            ->success()
            ->title('PDF Generated')
            ->body('Your report has been generated successfully!')
            ->send();

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'sales-report-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->form->getState();

        $sales = Sale::with('menu')
            ->whereBetween('transaction_date', [$data['start_date'], $data['end_date']])
            ->where('payment_status', 'paid')
            ->count();

        if ($sales === 0) {
            Notification::make()
                ->warning()
                ->title('No Data Found')
                ->body('No sales transactions found for the selected period.')
                ->send();
            return;
        }

        Notification::make()
            ->success()
            ->title('Excel Generated')
            ->body('Your report has been generated successfully!')
            ->send();

        return Excel::download(
            new SalesExport($data['start_date'], $data['end_date']),
            'sales-report-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function getReportSummary(): array
    {
        $data = $this->form->getState();

        $sales = Sale::with('menu')
            ->whereBetween('transaction_date', [$data['start_date'], $data['end_date']])
            ->where('payment_status', 'paid')
            ->get();

        return [
            'total_revenue' => $sales->sum('total_price'),
            'total_transactions' => $sales->count(),
            'total_items' => $sales->sum('quantity'),
        ];
    }
    public static function canAccess(): bool
{
    return auth()->user()->isOwner();
}
}
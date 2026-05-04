<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];
    
    // Properties untuk menyimpan data yang sudah di-load
    public ?array $reportData = null;
    public bool $dataLoaded = false;

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'category' => 'all',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan Penjualan')
                    ->description('Pilih periode dan kategori untuk laporan penjualan')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->required()
                            ->default(now()->endOfMonth())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('start_date'),

                        Select::make('category')
                            ->label('Kategori Menu')
                            ->options([
                                'all' => 'Semua Kategori',
                                'Snack' => 'Snack',
                                'Makanan' => 'Makanan',
                                'Rice Bowl' => 'Rice Bowl',
                                'Coffee' => 'Coffee',
                                'Non Coffee' => 'Non Coffee',
                                'Fresh' => 'Fresh',
                                'Manual Brew' => 'Manual Brew',
                            ])
                            ->default('all')
                            ->native(false),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

public function showData()
{
    $data = $this->form->getState();

    // Base query TANPA orderBy dulu
    $query = Sale::where('payment_status', 'paid')
        ->whereBetween('transaction_date', [
            $data['start_date'] . ' 00:00:00',
            $data['end_date'] . ' 23:59:59'
        ]);

    if ($data['category'] !== 'all') {
        $query->whereHas('menu', function ($q) use ($data) {
            $q->where('category', $data['category']);
        });
    }

    // Hitung summary SEBELUM take() — clone di sini masih bersih
    $totalRevenue      = (clone $query)->sum('total_price');
    $totalTransactions = (clone $query)->count();
    $totalItems        = (clone $query)->sum('quantity');

    if ($totalTransactions === 0) {
        Notification::make()
            ->warning()
            ->title('Tidak Ada Data')
            ->body('Tidak ada transaksi untuk periode yang dipilih.')
            ->send();

        $this->dataLoaded = false;
        $this->reportData = null;
        return;
    }

    // Preview: 10 baris + with('menu')
    $salesPreview = (clone $query)
        ->with('menu')
        ->orderBy('transaction_date', 'desc')
        ->take(10)
        ->get();

    // Export: semua data + with('menu')
    $salesAll = (clone $query)
        ->with('menu')
        ->orderBy('transaction_date', 'desc')
        ->get();

    $this->reportData = [
        'sales'              => $salesPreview,
        'sales_all'          => $salesAll,
        'total_revenue'      => $totalRevenue,
        'total_transactions' => $totalTransactions,
        'total_items'        => $totalItems,
        'start_date'         => $data['start_date'],
        'end_date'           => $data['end_date'],
        'category'           => $data['category'],
    ];

    $this->dataLoaded = true;

    Notification::make()
        ->success()
        ->title('Data Berhasil Dimuat')
        ->body($totalTransactions . ' transaksi ditemukan')
        ->send();
}
    public function exportPdf()
    {
        if (!$this->dataLoaded || !$this->reportData) {
            Notification::make()
                ->warning()
                ->title('Data Belum Dimuat')
                ->body('Klik "Tampilkan Data" terlebih dahulu')
                ->send();
            return;
        }

        $pdf = Pdf::loadView('reports.sales-pdf', [
            'sales' => $this->reportData['sales_all'],
            'startDate' => $this->reportData['start_date'],
            'endDate' => $this->reportData['end_date'],
            'totalRevenue' => $this->reportData['total_revenue'],
            'totalTransactions' => $this->reportData['total_transactions'],
            'totalItems' => $this->reportData['total_items'],
        ]);

        Notification::make()
            ->success()
            ->title('PDF Berhasil Dibuat')
            ->body('Laporan PDF siap diunduh!')
            ->send();

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'laporan-penjualan-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function exportExcel()
    {
        if (!$this->dataLoaded || !$this->reportData) {
            Notification::make()
                ->warning()
                ->title('Data Belum Dimuat')
                ->body('Klik "Tampilkan Data" terlebih dahulu')
                ->send();
            return;
        }

        Notification::make()
            ->success()
            ->title('Excel Berhasil Dibuat')
            ->body('Laporan Excel siap diunduh!')
            ->send();

        return Excel::download(
            new SalesExport($this->reportData['start_date'], $this->reportData['end_date']),
            'laporan-penjualan-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isOwner();
    }
}
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Models\Sale;
use App\Models\DailyClosing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;  // 👈 ADD THIS LINE
use Illuminate\Support\Facades\Artisan;  // 👈 THIS ONE
class DailyCashClosing extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static string $view = 'filament.pages.daily-cash-closing';

    protected static ?string $navigationGroup = 'Daily Operations';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Daily Cash Closing';

    public ?array $data = [];

    // Only Admin can access
    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Daily Closing Notes')
                    ->description('Optional notes for this closing')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Enter any notes or remarks for today\'s closing...')
                            ->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

   public function closeDay()
{
    // Check if there are transactions since last closing
    $summary = Sale::getTodaySummary();

    // Validate there are transactions
    if ($summary['total_transactions'] === 0) {
        Notification::make()
            ->warning()
            ->title('Tidak Ada Transaksi')
            ->body('Tidak dapat menutup kas tanpa transaksi.')
            ->send();
        return;
    }

    $data = $this->form->getState();

    try {
        DB::beginTransaction();

        // Get next operating day number
        $operatingDay = DailyClosing::getCurrentOperatingDay();

        // Create closing record
        DailyClosing::create([
            'closing_date' => now()->toDateString(),
            'operating_day' => $operatingDay,
            'is_manually_started' => false,
            'total_revenue' => $summary['total_revenue'],
            'total_transactions' => $summary['total_transactions'],
            'total_items' => $summary['total_items'],
            'closed_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Clear cache
        Cache::forget('current_operating_day');
        Cache::forget('is_today_closed_' . now()->toDateString());

        DB::commit();

        Notification::make()
            ->success()
            ->title('Kas Berhasil Ditutup')
            ->body('Kas hari operasional #' . $operatingDay . ' telah ditutup dan dikunci.')
            ->send();

        $this->form->fill();
        redirect()->to(static::getUrl());

    } catch (\Exception $e) {
        DB::rollBack();

        Notification::make()
            ->danger()
            ->title('Error')
            ->body('Gagal menutup kas: ' . $e->getMessage())
            ->send();
    }
}
   
    public function startNewDay()
{
    // Check if can start new day
    if (!DailyClosing::canStartNewDay()) {
        Notification::make()
            ->warning()
            ->title('Tidak Dapat Memulai Hari Baru')
            ->body('Silakan tutup kas terlebih dahulu.')
            ->send();
        return;
    }

    // Mark the last closing as "new day started"
    DailyClosing::startNewOperatingDay();

    Notification::make()
        ->success()
        ->title('🎉 Hari Baru Dimulai!')
        ->body('Revenue telah di-reset ke Rp 0.')
        ->duration(3000)
        ->send();

    // Force reload to dashboard
    $this->js('setTimeout(function(){ window.location.href = "/admin"; }, 1000);');
}

public function isTodayClosed(): bool
{
    // Use the new method that checks if we're currently closed
    return DailyClosing::isCurrentlyClosed();
}

public function canStartNewDay(): bool
{
    return DailyClosing::canStartNewDay();
}

public function getTodaySummary(): array
{
    return Sale::getTodaySummary();
}

public function getLastClosing(): ?DailyClosing
{
    return DailyClosing::latest('created_at')->first();
}

public function getCurrentOperatingDay(): int
{
    return DailyClosing::getCurrentOperatingDay();
}

public function downloadPdf()
{
    $lastClosing = DailyClosing::latest('created_at')->first();

    if (!$lastClosing) {
        Notification::make()
            ->warning()
            ->title('Tidak Ada Data')
            ->body('Belum ada penutupan kas.')
            ->send();
        return;
    }

    $sales = Sale::with('menu')
        ->where('payment_status', 'paid')
        ->where('transaction_date', '>', 
            DailyClosing::latest('created_at')->skip(1)->first()?->created_at 
            ?? $lastClosing->closing_date->startOfDay()
        )
        ->where('transaction_date', '<=', $lastClosing->created_at)
        ->orderBy('transaction_date', 'asc')
        ->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily-closing-pdf', [
        'closing' => $lastClosing,
        'sales' => $sales,
    ]);

    return response()->streamDownload(
        fn () => print($pdf->output()),
        'laporan-harian-' . $lastClosing->closing_date->format('Y-m-d') . '-op' . $lastClosing->operating_day . '.pdf'
    );
}

public function downloadExcel()
{
    $lastClosing = DailyClosing::latest('created_at')->first();

    if (!$lastClosing) {
        Notification::make()
            ->warning()
            ->title('Tidak Ada Data')
            ->body('Belum ada penutupan kas.')
            ->send();
        return;
    }

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\DailyClosingExport($lastClosing),
        'laporan-harian-' . $lastClosing->closing_date->format('Y-m-d') . '-op' . $lastClosing->operating_day . '.xlsx'
    );
}
}
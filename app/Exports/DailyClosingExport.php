<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\DailyClosing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyClosingExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    protected DailyClosing $closing;

    public function __construct(DailyClosing $closing)
    {
        $this->closing = $closing;
    }

    public function collection()
    {
        // Get previous closing to determine period start
        $previousClosing = DailyClosing::where('id', '<', $this->closing->id)
            ->latest('created_at')
            ->first();

        $startTime = $previousClosing 
            ? $previousClosing->created_at 
            : $this->closing->closing_date->startOfDay();

        return Sale::with('menu')
            ->where('payment_status', 'paid')
            ->where('transaction_date', '>', $startTime)
            ->where('transaction_date', '<=', $this->closing->created_at)
            ->orderBy('transaction_date', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Waktu Transaksi',
            'Nama Menu',
            'Kategori',
            'Jumlah',
            'Harga Satuan',
            'Total Harga',
        ];
    }

    public function map($sale): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $sale->transaction_date->timezone('Asia/Makassar')->format('d/m/Y H:i'),
            $sale->menu->name,
            $sale->menu->category,
            $sale->quantity,
            'Rp ' . number_format($sale->menu->price, 0, ',', '.'),
            'Rp ' . number_format($sale->total_price, 0, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F59E0B']],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 25,
            'D' => 15,
            'E' => 10,
            'F' => 18,
            'G' => 18,
        ];
    }

    public function title(): string
    {
        return 'Laporan Hari #' . $this->closing->operating_day;
    }
}
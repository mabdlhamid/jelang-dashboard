<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ?? now()->startOfMonth();
        $this->endDate = $endDate ?? now()->endOfMonth();
    }

    public function collection()
    {
        return Sale::with('menu')
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->where('payment_status', 'paid')
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Transaction Date',
            'Menu Name',
            'Category',
            'Quantity',
            'Unit Price',
            'Total Price',
            'Payment Status',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->transaction_date->format('d/m/Y H:i'),
            $sale->menu->name,
            $sale->menu->category,
            $sale->quantity,
            'Rp ' . number_format($sale->menu->price, 0, ',', '.'),
            'Rp ' . number_format($sale->total_price, 0, ',', '.'),
            ucfirst($sale->payment_status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
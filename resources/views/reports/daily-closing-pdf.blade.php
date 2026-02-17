<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        
        .header { 
            text-align: center; 
            padding: 20px 0; 
            border-bottom: 2px solid #F59E0B; 
            margin-bottom: 20px; 
        }
        .header h1 { font-size: 20px; color: #F59E0B; }
        .header h2 { font-size: 15px; margin: 5px 0; }
        .header p { font-size: 11px; color: #666; }

        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 15px;
            background: #FEF3C7;
            border: 1px solid #F59E0B;
        }
        .summary-item .label { font-size: 10px; color: #666; margin-bottom: 5px; }
        .summary-item .value { font-size: 16px; font-weight: bold; color: #333; }

        .info-box {
            background: #f9f9f9;
            padding: 10px 15px;
            border-left: 4px solid #F59E0B;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .info-box p { margin: 3px 0; }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        thead tr { background-color: #F59E0B; }
        thead th { 
            color: white; 
            padding: 8px; 
            text-align: left; 
            font-size: 11px; 
        }
        tbody td { 
            padding: 7px 8px; 
            border-bottom: 1px solid #eee; 
            font-size: 11px; 
        }
        tbody tr:nth-child(even) { background: #fafafa; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-row {
            background: #FEF3C7 !important;
            font-weight: bold;
        }

        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px; 
            color: #999; 
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>☕ Café BI Dashboard</h1>
        <h2>Laporan Harian - Hari Operasional #{{ $closing->operating_day }}</h2>
        <p>Tanggal: {{ $closing->closing_date->timezone('Asia/Makassar')->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>

    {{-- Summary Cards --}}
    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($closing->total_revenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ number_format($closing->total_transactions) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Barang Terjual</div>
            <div class="value">{{ number_format($closing->total_items) }}</div>
        </div>
    </div>

    {{-- Closing Info --}}
    <div class="info-box">
        <p><strong>Ditutup Oleh:</strong> {{ $closing->closedBy->name }}</p>
        <p><strong>Waktu Tutup:</strong> {{ $closing->created_at->timezone('Asia/Makassar')->format('H:i:s') }} WITA</p>
        @if($closing->notes)
        <p><strong>Catatan:</strong> {{ $closing->notes }}</p>
        @endif
    </div>

    {{-- Transaction Table --}}
    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Waktu</th>
                <th>Menu</th>
                <th>Kategori</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $index => $sale)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $sale->transaction_date->timezone('Asia/Makassar')->format('H:i') }}</td>
                <td>{{ $sale->menu->name }}</td>
                <td>{{ $sale->menu->category }}</td>
                <td class="text-center">{{ $sale->quantity }}</td>
                <td class="text-right">Rp {{ number_format($sale->menu->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach

            {{-- Total Row --}}
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ $sales->sum('quantity') }}</strong></td>
                <td></td>
                <td class="text-right"><strong>Rp {{ number_format($sales->sum('total_price'), 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->timezone('Asia/Makassar')->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WITA</p>
        <p>Café BI Dashboard - Laporan ini dibuat secara otomatis oleh sistem</p>
    </div>

</body>
</html>
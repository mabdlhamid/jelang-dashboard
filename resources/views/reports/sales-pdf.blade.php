<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f8b400;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>☕ Cafe BI Dashboard</h1>
        <h2>Laporan Kuangan</h2>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->timezone('Asia/Makassar')->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->timezone('Asia/Makassar')->format('d M Y') }}</p>        <p>Generated: {{ now()->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA</p>
    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Revenue</div>
                <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Transactions</div>
                <div class="value">{{ number_format($totalTransactions) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Items Sold</div>
                <div class="value">{{ number_format($totalItems) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Menu</th>
                <th>Category</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->transaction_date->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->menu->name }}</td>
                <td>{{ $sale->menu->category }}</td>
                <td class="text-right">{{ $sale->quantity }}</td>
                <td class="text-right">Rp {{ number_format($sale->menu->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by Café BI Dashboard System</p>
    </div>
</body>
</html>
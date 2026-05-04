<x-filament-panels::page>

    {{-- Filter Form --}}
    <form wire:submit.prevent="showData">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg" icon="heroicon-o-magnifying-glass">
                🔍 Tampilkan Data
            </x-filament::button>
        </div>
    </form>

    {{-- Data Preview --}}
    @if($dataLoaded && $reportData)
        
        {{-- Summary Cards --}}
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600 dark:text-success-400">
                        Rp {{ number_format($reportData['total_revenue'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        💰 Total Pendapatan
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600 dark:text-info-400">
                        {{ number_format($reportData['total_transactions']) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        🛒 Total Transaksi
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600 dark:text-warning-400">
                        {{ number_format($reportData['total_items']) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        📦 Total Barang Terjual
                    </div>
                </div>
            </x-filament::section>

        </div>

        {{-- Data Table --}}
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                📋 Detail Transaksi
            </x-slot>
            
            <x-slot name="description">
                Menampilkan {{ number_format($reportData['total_transactions']) }} transaksi dari {{ \Carbon\Carbon::parse($reportData['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reportData['end_date'])->format('d/m/Y') }}
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">No</th>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal & Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold">Menu</th>
                            <th class="px-4 py-3 text-left font-semibold">Kategori</th>
                            <th class="px-4 py-3 text-right font-semibold">Qty</th>
                            <th class="px-4 py-3 text-right font-semibold">Harga Satuan</th>
                            <th class="px-4 py-3 text-right font-semibold">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($reportData['sales'] as $index => $sale)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $sale->transaction_date->timezone('Asia/Makassar')->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $sale->transaction_date->timezone('Asia/Makassar')->format('H:i') }} WITA</div>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $sale->menu->name }}</td>
                            <td class="px-4 py-3">
                                <x-filament::badge 
                                    :color="match($sale->menu->category) {
                                        'Snack' => 'warning',
                                        'Makanan' => 'danger',
                                        'Rice Bowl' => 'rose',
                                        'Coffee' => 'success',
                                        'Non Coffee' => 'info',
                                        'Fresh' => 'primary',
                                        'Manual Brew' => 'purple',
                                        default => 'gray',
                                    }">
                                    {{ $sale->menu->category }}
                                </x-filament::badge>
                            </td>
                            <td class="px-4 py-3 text-right">{{ $sale->quantity }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($sale->menu->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 dark:bg-gray-800 font-bold">
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-right text-lg">TOTAL:</td>
                            <td class="px-4 py-4 text-right text-lg text-success-600 dark:text-success-400">
                                Rp {{ number_format($reportData['total_revenue'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-filament::section>

        {{-- Export Buttons --}}
        <div class="mt-6 flex flex-wrap gap-3">
            <x-filament::button 
                wire:click="exportPdf" 
                color="danger"
                icon="heroicon-o-document-text"
                size="lg">
                📄 Download PDF
            </x-filament::button>

            <x-filament::button 
                wire:click="exportExcel" 
                color="success"
                icon="heroicon-o-document-arrow-down"
                size="lg">
                📊 Download Excel
            </x-filament::button>
        </div>

    @else
        
        {{-- Empty State --}}
        <x-filament::section class="mt-8">
            <div class="text-center py-16">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                    📊 Belum Ada Data yang Ditampilkan
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pilih tanggal dan kategori, lalu klik tombol "Tampilkan Data" untuk melihat laporan penjualan
                </p>
            </div>
        </x-filament::section>

    @endif

</x-filament-panels::page>
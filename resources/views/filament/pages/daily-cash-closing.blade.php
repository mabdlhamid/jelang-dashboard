<x-filament-panels::page>
    @php
        $summary = $this->getTodaySummary();
        $canStartNew = $this->canStartNewDay();
        $lastClosing = $this->getLastClosing();
        $currentOperatingDay = $this->getCurrentOperatingDay();
    @endphp

    <!-- Status Banner -->
    @if($canStartNew)
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded">
            <div class="flex items-center">
                <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 mr-3" />
                <div>
                    <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">
                        ✅ Siap Untuk Hari Baru
                    </h3>
                    <p class="text-sm text-green-600 dark:text-green-300">
                        Kas sudah ditutup. Klik "Mulai Hari Baru" untuk memulai operasional baru.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 rounded">
            <div class="flex items-center">
                <x-heroicon-o-information-circle class="w-6 h-6 text-blue-500 mr-3" />
                <div>
                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">
                        📊 Hari Operasional #{{ $currentOperatingDay }}
                    </h3>
                    <p class="text-sm text-blue-600 dark:text-blue-300">
                        Kas sedang aktif. Tutup kas untuk menyelesaikan hari operasional ini.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Today's Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Saat Ini</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white">
                Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">Transaksi Saat Ini</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ number_format($summary['total_transactions']) }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">Barang Terjual</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ number_format($summary['total_items']) }}
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($canStartNew)
        <!-- Show Start New Day Button -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            @if($lastClosing)
                <h3 class="text-lg font-semibold mb-4">Penutupan Terakhir</h3>
                
                <div class="space-y-2 text-sm mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Hari Operasional:</span>
                        <span class="font-semibold">#{{ $lastClosing->operating_day }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ditutup Pada:</span>
                        <span class="font-semibold">{{ $lastClosing->created_at->timezone('Asia/Makassar')->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }} WITA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Pendapatan:</span>
                        <span class="font-semibold">Rp {{ number_format($lastClosing->total_revenue, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Transaksi:</span>
                        <span class="font-semibold">{{ number_format($lastClosing->total_transactions) }}</span>
                    </div>
                </div>
            @endif

            {{-- Download Report Buttons --}}
<div class="mb-6">
    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
        📥 Unduh Laporan Hari Operasional #{{ $lastClosing->operating_day }}
    </h4>
    <div class="flex gap-3">
        <x-filament::button
            type="button"
            wire:click="downloadPdf"
            color="danger"
            icon="heroicon-o-document-text"
            size="md"
        >
            📄 Download PDF
        </x-filament::button>

        <x-filament::button
            type="button"
            wire:click="downloadExcel"
            color="success"
            icon="heroicon-o-document-arrow-down"
            size="md"
        >
            📊 Download Excel
        </x-filament::button>
    </div>
    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
        💡 Laporan akan otomatis terunduh ke folder Downloads Anda.
    </p>
</div>

<div class="border-t border-gray-200 dark:border-gray-700 pt-6">
    <x-filament::button
        type="button"
        wire:click="startNewDay"
        color="success"
        icon="heroicon-o-arrow-right"
        size="lg"
    >
        ▶️ Mulai Hari Baru (Hari #{{ $currentOperatingDay }})
    </x-filament::button>

    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        <p>💡 Revenue akan otomatis menjadi Rp 0 setelah Anda memulai hari baru.</p>
    </div>
</div>

            <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                <p>💡 Revenue akan otomatis menjadi Rp 0 setelah Anda memulai hari baru.</p>
            </div>
        </div>
    @else
        <!-- Show Close Day Form -->
        <form wire:submit="closeDay">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button
                    type="submit"
                    color="danger"
                    icon="heroicon-o-lock-closed"
                    size="lg"
                >
                    🔒 Tutup Kas Hari Operasional #{{ $currentOperatingDay }}
                </x-filament::button>

                <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    <p>⚠️ Peringatan: Setelah ditutup, transaksi pada periode ini tidak dapat diedit atau dihapus.</p>
                </div>
            </div>
        </form>
    @endif
</x-filament-panels::page>
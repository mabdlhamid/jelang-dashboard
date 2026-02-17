<x-filament-panels::page>
    <form wire:submit="exportPdf">
        {{ $this->form }}

        <div class="mt-6">
            <!-- Summary Cards -->
            @php
                $summary = $this->getReportSummary();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Transactions</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($summary['total_transactions']) }}
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Items Sold</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($summary['total_items']) }}
                    </div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-4">
                <x-filament::button
                    type="button"
                    wire:click="exportPdf"
                    color="danger"
                    icon="heroicon-o-document-text"
                    size="lg"
                >
                    Export to PDF
                </x-filament::button>

                <x-filament::button
                    type="button"
                    wire:click="exportExcel"
                    color="success"
                    icon="heroicon-o-document-arrow-down"
                    size="lg"
                >
                    Export to Excel
                </x-filament::button>
            </div>

            <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                <p>💡 Tip: Reports will download automatically to your browser's download folder.</p>
            </div>
        </div>
    </form>
</x-filament-panels::page>
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            
            {{-- Header --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        💡 Business Insights
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Analisis otomatis berdasarkan data penjualan
                    </p>
                </div>
            </div>

            {{-- Insights Grid --}}
            @php
                $insights = $this->getInsights();
            @endphp

            @if(count($insights) > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($insights as $insight)
                        <div class="relative group">
                            {{-- Hover gradient effect --}}
                            <div class="absolute inset-0 bg-gradient-to-r {{ $this->getGradient($insight['type']) }} rounded-xl opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                            
                            {{-- Card content --}}
                            <div class="relative p-5 rounded-xl border-2 {{ $this->getBorderColor($insight['type']) }} bg-white dark:bg-gray-800 hover:shadow-lg transition-all duration-300">
                                
                                {{-- Icon & Title --}}
                                <div class="flex items-start gap-4 mb-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $this->getBgColor($insight['type']) }} flex items-center justify-center text-2xl shadow-sm">
                                        {{ $insight['icon'] }}
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-1.5">
                                            {{ $insight['title'] }}
                                        </h4>
                                        <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $this->getBadgeColor($insight['type']) }}">
                                            {{ ucfirst($insight['type']) }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Message --}}
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100 mb-3 leading-relaxed">                                    {{ $insight['message'] }}
                                </p>

                                {{-- Recommendation --}}
                                @if(!empty($insight['recommendation']))
                                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-xs text-gray-600 dark:text-gray-400 italic">
                                                {{ $insight['recommendation'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- No insights state --}}
                <div class="text-center py-12">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                        Tidak Ada Insight Saat Ini
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Sistem akan menampilkan insight otomatis saat terdapat perubahan signifikan
                    </p>
                </div>
            @endif

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
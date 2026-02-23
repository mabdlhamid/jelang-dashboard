<x-filament-panels::page>

    @php
        $user = auth()->user();
        $hour = now()->timezone('Asia/Makassar')->hour;

        $timeGreeting = match(true) {
            $hour >= 5 && $hour < 12 => 'Selamat Pagi',
            $hour >= 12 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };

        $roleLabel = $user->isOwner() ? 'Owner' : 'Admin';

        $description = $user->isOwner()
            ? 'Berikut adalah ringkasan performa bisnis cafe Anda'
            : 'Siap memulai aktivitas operasional hari ini';

        $isClosed = $user->isAdmin()
            ? \App\Models\DailyClosing::isCurrentlyClosed()
            : null;
    @endphp

    {{-- Welcome Header --}}
    <div class="rounded-2xl overflow-hidden shadow-lg mb-6"
        style="background: linear-gradient(135deg, #f8b400 0%, #f59e0b 50%, #d97706 100%);">

        {{-- ... existing welcome header code ... --}}

    </div>

    {{-- Greeting Section --}}
    {{-- Greeting Section - Mobile Optimized --}}
<x-filament::section>

    {{-- Desktop & Mobile Layout --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        {{-- Left: Logo + Greeting --}}
        <div class="flex items-center gap-3 md:gap-4 flex-1 min-w-0">

            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}"
                    alt="Logo"
        class="w-12 h-12 md:w-12 md:h-12 rounded-lg object-contain flex-shrink-0">
            @endif

            <div class="min-w-0 flex-1">
                <h2 class="text-lg md:text-xl font-bold break-words">
                    {{ $timeGreeting }}, {{ $user->name }}
                </h2>

                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                    {{ $description }}
                </p>
            </div>
        </div>

        {{-- Right: Date - Responsive --}}
        <div class="text-left md:text-right text-xs md:text-sm text-gray-500 dark:text-gray-400 flex-shrink-0">
            <div>
                {{ now()->timezone('Asia/Makassar')->locale('id')->isoFormat('dddd') }}
            </div>
            <div class="font-semibold text-gray-900 dark:text-white">
                {{ now()->timezone('Asia/Makassar')->locale('id')->isoFormat('D MMMM Y') }}
            </div>
        </div>

    </div>

    {{-- Badges - Responsive spacing --}}
    <div class="flex items-center gap-2 mt-3 ml-0 md:ml-14">

        <x-filament::badge>
            {{ $roleLabel }}
        </x-filament::badge>

        @if($user->isAdmin() && $isClosed !== null)
            <x-filament::badge :color="$isClosed ? 'danger' : 'success'">
                {{ $isClosed ? 'Kas Ditutup' : 'Kas Aktif' }}
            </x-filament::badge>
        @endif

    </div>

</x-filament::section>

    {{-- ✅ FILTER (POSISI IDEAL) --}}
   @if($user->isOwner())
    <div class="mb-6">
        @livewire('analytics-filter')
    </div>
@endif


    {{-- ✅ Widgets (HANYA SEKALI 🔥) --}}
    <x-filament-widgets::widgets
        :widgets="$this->getWidgets()"
        :columns="$this->getColumns()"
    />

</x-filament-panels::page>

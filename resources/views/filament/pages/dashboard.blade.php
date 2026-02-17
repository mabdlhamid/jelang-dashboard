<x-filament-panels::page>

    {{-- Welcome Header --}}
    <div class="rounded-2xl overflow-hidden shadow-lg mb-6"
        style="background: linear-gradient(135deg, #f8b400 0%, #f59e0b 50%, #d97706 100%);">
        <div class="px-8 py-6 flex items-center justify-between">

            {{-- Left: Logo + Welcome Text --}}
            <div class="flex items-center gap-5">

                {{-- Café Logo --}}
                <div class="flex-shrink-0">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Café Logo"
                        class="h-16 w-16 rounded-full object-cover shadow-md border-2 border-white"
                        onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='flex';"
                    >
                    {{-- Fallback if no logo --}}
                    <div id="logo-fallback"
                        style="display:none; width:64px; height:64px; background:white; border-radius:50%; align-items:center; justify-content:center; font-size:28px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        ☕
                    </div>
                </div>

                {{-- Welcome Text --}}
                <div>
                    @if(auth()->user()->isOwner())
                        <h1 class="text-2xl font-bold text-white">
                            Selamat Datang, Owner! 👋
                        </h1>
                        <p class="text-yellow-100 text-sm mt-1">
                            Berikut adalah ringkasan bisnis café Anda hari ini.
                        </p>
                    @else
                        <h1 class="text-2xl font-bold text-white">
                            Selamat Datang, Admin! 👋
                        </h1>
                        <p class="text-yellow-100 text-sm mt-1">
                            Siap memulai aktivitas kasir hari ini?
                        </p>
                    @endif
                </div>
            </div>

            {{-- Right: Date + Time + Status --}}
            <div class="text-right hidden md:block">
                <div class="text-white font-semibold text-lg">
                    {{ now()->timezone('Asia/Makassar')->locale('id')->isoFormat('dddd') }}
                </div>
                <div class="text-yellow-100 text-sm">
                    {{ now()->timezone('Asia/Makassar')->locale('id')->isoFormat('D MMMM Y') }}
                </div>
                <div class="text-yellow-100 text-sm mt-1">
                    🕐 {{ now()->timezone('Asia/Makassar')->format('H:i') }} WITA
                </div>

                {{-- Admin: Show Day Status --}}
                @if(auth()->user()->isAdmin())
                    @php
                        $isClosed = \App\Models\DailyClosing::isCurrentlyClosed();
                    @endphp
                    <div class="mt-2">
                        @if($isClosed)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                🔒 Kas Ditutup
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                ✅ Kas Aktif
                            </span>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Widgets --}}
    <x-filament-widgets::widgets
        :widgets="$this->getWidgets()"
        :columns="$this->getColumns()"
    />

</x-filament-panels::page>
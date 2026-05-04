<x-filament-panels::page.simple>

    <style>
        /* PERLUAS LAYOUT */
        .fi-simple-main {
            max-width: 900px !important;
        }
.fi-input-wrp {
    border-radius: 10px !important;
    border: 1px solid rgba(0,0,0,0.08) !important;
    background: rgba(255,255,255,0.7) !important;
}

.dark .fi-input-wrp {
    background: rgba(255,255,255,0.04) !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
}

.fi-input-wrp:focus-within {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59,130,246,0.2);
}
        /* CONTAINER */
     .login-wrapper {
    display: flex;
    border-radius: 18px;
    overflow: hidden;
    background: linear-gradient(145deg, #ffffff, #f3f4f6);
    box-shadow:
        0 20px 60px rgba(0,0,0,0.1),
        0 0 0 1px rgba(0,0,0,0.04);
}

.dark .login-wrapper {
    background: linear-gradient(145deg, #0f0f17, #0a0a12);
    box-shadow:
        0 30px 80px rgba(0,0,0,0.7),
        0 0 0 1px rgba(255,255,255,0.05);
}

        /* LEFT (FORM) */
        .login-left {
            width: 100%;
            max-width: 420px;
            padding: 32px;
        }

        /* RIGHT (VISUAL) */
        .login-right {
            flex: 1;
            display: none;
    background: radial-gradient(circle at top left, #3b82f6, #1e293b 60%);
            color: white;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
                position: relative;

        }
        .login-right::before {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    background: #60a5fa;
    opacity: 0.2;
    filter: blur(100px);
    top: -50px;
    right: -50px;
}

        .login-right h2 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-right p {
            font-size: 14px;
            color: #cbd5f5;
        }

        /* RESPONSIVE */
        @media (min-width: 768px) {
            .login-right {
                display: flex;
            }
        }

        /* INPUT */
        .fi-input-wrp {
            border-radius: 10px !important;
        }

        /* BUTTON */
    button[type="submit"] {
    height: 44px !important;
    border-radius: 10px !important;
    font-weight: 600;
    background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
    color: white !important;
    box-shadow: 0 10px 25px rgba(59,130,246,0.3);
}

button[type="submit"]:hover {
    transform: translateY(-1px);
}
    </style>

    <div class="login-wrapper">

        <!-- LEFT -->
        <div class="login-left">

            <div class="text-center mb-6">
                @if (is_callable($brandLogo = filament()->getBrandLogo()))
                    <div class="flex justify-center mb-3">
                        {{ $brandLogo() }}
                    </div>
                @endif

                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ filament()->getBrandName() }}
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Silakan login untuk melanjutkan
                </p>
            </div>

            <!-- FORM -->
            <x-filament-panels::form wire:submit.prevent="authenticate">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    full-width
                    class="mt-5"
                />
            </x-filament-panels::form>

        </div>

        <!-- RIGHT -->
        <div class="login-right">
            <div>
                <div class="text-4xl mb-4">🚀</div>
                <h2>Dashboard Modern</h2>
                <p>
                    Kelola sistem Anda dengan mudah, cepat, dan aman dalam satu platform.
                </p>
            </div>
        </div>

    </div>

</x-filament-panels::page.simple>
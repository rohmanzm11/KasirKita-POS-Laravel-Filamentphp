<x-filament-widgets::widget>
    @php
        $user    = $this->getUser();
        $tenant  = $this->getTenant();
        $initial = strtoupper(substr($user->name, 0, 1));
        $jamNow  = now()->hour;
        $isMalam = $jamNow < 5 || $jamNow >= 18;
    @endphp

    <div
        class="welcome-card"
        x-data="{
            time: '',
            date: '',
            tick() {
                const now = new Date();
                this.time = now.toLocaleTimeString('id-ID');
                this.date = now.toLocaleDateString('id-ID', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                });
            }
        }"
        x-init="tick(); setInterval(() => tick(), 1000)"
    >
        <div class="welcome-card__pattern"></div>
        <div class="welcome-card__blur welcome-card__blur--1"></div>
        <div class="welcome-card__blur welcome-card__blur--2"></div>

        <div class="welcome-card__icon">
            @if ($isMalam)
                <x-heroicon-o-moon class="h-16 w-16" />
            @else
                <x-heroicon-o-sun class="h-16 w-16" />
            @endif
        </div>

        <div class="welcome-card__inner">
            {{-- KIRI: logo tenant + sapaan --}}
            <div class="welcome-card__left">
                <div class="welcome-card__avatar-wrap">
                    @if ($tenant?->logo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($tenant->logo) }}"
                             alt="{{ $tenant->nama }}"
                             class="welcome-card__avatar-img" />
                    @else
                        <div class="welcome-card__avatar-fallback">
                            {{ $initial }}
                        </div>
                    @endif

                    <span class="welcome-card__status">
                        <span class="welcome-card__status-dot"></span>
                    </span>
                </div>

                <div>
                    <p class="welcome-card__sapaan">{{ $this->getSapaan() }},</p>
                    <h2 class="welcome-card__nama">{{ $user->name }} 👋</h2>

                    <div class="welcome-card__badges">
                        @if ($tenant)
                            <span class="welcome-card__badge">
                                <x-heroicon-m-building-storefront class="h-3.5 w-3.5" />
                                {{ $tenant->nama }}
                            </span>
                        @endif

                        <span class="welcome-card__badge">
                            <span class="welcome-card__badge-dot"></span>
                            Online
                        </span>
                    </div>
                </div>
            </div>

            {{-- KANAN: jam live (Alpine) --}}
            <div class="welcome-card__right">
                <p class="welcome-card__clock" x-text="time">--:--:--</p>
                <p class="welcome-card__date" x-text="date"></p>
            </div>
        </div>
    </div>

    <style>
        .welcome-card {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1.5rem;
            color: #ffffff;
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 55%, #22d3ee 100%);
            box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.35);
        }

        @media (min-width: 640px) {
            .welcome-card { padding: 2rem; }
        }

        .dark .welcome-card {
            background: linear-gradient(135deg, #075985 0%, #0369a1 55%, #0891b2 100%);
        }

        .welcome-card__pattern {
            position: absolute;
            inset: 0;
            opacity: 0.08;
            background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
            background-size: 18px 18px;
            pointer-events: none;
        }

        .welcome-card__blur {
            position: absolute;
            border-radius: 9999px;
            filter: blur(48px);
            pointer-events: none;
        }
        .welcome-card__blur--1 {
            top: -3rem;
            right: -3rem;
            width: 12rem;
            height: 12rem;
            background: rgba(255, 255, 255, 0.15);
        }
        .welcome-card__blur--2 {
            bottom: -4rem;
            left: -2rem;
            width: 9rem;
            height: 9rem;
            background: rgba(103, 232, 249, 0.25);
        }

        .welcome-card__icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            opacity: 0.2;
            pointer-events: none;
        }
        @media (min-width: 640px) {
            .welcome-card__icon { top: 2rem; right: 2rem; }
        }

        .welcome-card__inner {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .welcome-card__inner {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .welcome-card__left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .welcome-card__avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .welcome-card__avatar-img,
        .welcome-card__avatar-fallback {
            width: 4rem;
            height: 4rem;
            border-radius: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        .welcome-card__avatar-img { object-fit: cover; }
        .welcome-card__avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
        }

        .welcome-card__status {
            position: absolute;
            bottom: -0.25rem;
            right: -0.25rem;
            width: 1rem;
            height: 1rem;
            border-radius: 9999px;
            background: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8);
        }
        .welcome-card__status-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background: #34d399;
            animation: welcome-pulse 1.6s ease-in-out infinite;
        }

        @keyframes welcome-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .welcome-card__sapaan {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }

        .welcome-card__nama {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.01em;
            margin: 0.125rem 0 0;
        }

        .welcome-card__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .welcome-card__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
        }

        .welcome-card__badge-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
            background: #34d399;
        }

        .welcome-card__right {
            text-align: right;
        }

        .welcome-card__clock {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
            margin: 0;
        }

        .welcome-card__date {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.75);
        }
    </style>

    <script>
        function updateWelcomeClock() {
            const now   = new Date();
            const clock = document.getElementById('live-clock');
            const date  = document.getElementById('live-date');
            if (!clock || !date) return;

            clock.textContent = now.toLocaleTimeString('id-ID');
            date.textContent  = now.toLocaleDateString('id-ID', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        }
        updateWelcomeClock();
        setInterval(updateWelcomeClock, 1000);
    </script>
</x-filament-widgets::widget>
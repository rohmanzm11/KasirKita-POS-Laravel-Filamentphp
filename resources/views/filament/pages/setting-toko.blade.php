<x-filament-panels::page>
    <style>
        .setting-toko-wrapper {
            max-width: 1100px;
            margin: 0 auto;

            --st-primary-600: rgb(var(--primary-600));
            --st-primary-700: rgb(var(--primary-700));
            --st-primary-800: rgb(var(--primary-800));
            --st-primary-shadow: rgb(var(--primary-600) / 0.35);

            /* Filament v4:
            --st-primary-600: var(--primary-600);
            --st-primary-700: var(--primary-700);
            --st-primary-800: var(--primary-800);
            --st-primary-shadow: color-mix(in srgb, var(--primary-600) 35%, transparent);
            */
        }

        .setting-toko-card {
            background: white;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.06), 0 1px 2px 0 rgb(0 0 0 / 0.04);
            overflow: hidden;
        }

        .dark .setting-toko-card {
            background: #1f2937;
            border-color: #374151;
        }

        .setting-toko-header {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            background: linear-gradient(135deg, var(--st-primary-600) 0%, var(--st-primary-800) 100%);
            padding: 1.5rem 2rem;
            color: white;
        }

        .setting-toko-header-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            flex-shrink: 0;
            border-radius: 0.75rem;
            background: rgb(255 255 255 / 0.15);
        }

        .setting-toko-header-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .setting-toko-header h2 {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }

        .setting-toko-header p {
            margin: 0.125rem 0 0;
            opacity: 0.85;
            font-size: 0.8125rem;
        }

        .setting-toko-body {
            padding: 2rem;
        }

        /* Jarak antar Section Filament dirapikan */
        .setting-toko-body > div > .fi-section:not(:last-child) {
            margin-bottom: 1.5rem;
        }

        .setting-toko-footer {
            padding: 1.125rem 2rem;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.75rem;
        }

        .dark .setting-toko-footer {
            background: #111827;
            border-color: #374151;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.5rem;
            background: var(--st-primary-600);
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-save:hover {
            background: var(--st-primary-700);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--st-primary-shadow);
        }

        .btn-save:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .btn-save svg {
            width: 1.125rem;
            height: 1.125rem;
        }

        /* Rapikan spacing komponen form Filament di dalam card */
        .setting-toko-body .fi-fo-component-ctn {
            margin-bottom: 0 !important;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .setting-toko-wrapper {
                max-width: 100%;
            }

            .setting-toko-header {
                padding: 1.25rem 1.25rem;
            }

            .setting-toko-body {
                padding: 1.25rem;
            }

            .setting-toko-footer {
                padding: 1rem 1.25rem;
            }

            .btn-save {
                width: 100%;
            }
        }
    </style>

    <div class="setting-toko-wrapper">
        <div class="setting-toko-card">
            {{-- Header --}}
            <div class="setting-toko-header">
                <div class="setting-toko-header-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                    </svg>
                </div>
                <div>
                    <h2>Setting Toko</h2>
                    <p>Kelola informasi toko Anda di sini</p>
                </div>
            </div>

            {{-- Body Form --}}
            <form wire:submit="save">
                <div class="setting-toko-body">
                    {{ $this->form }}
                </div>

                {{-- Footer --}}
                <div class="setting-toko-footer">
                    <button type="submit" class="btn-save">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
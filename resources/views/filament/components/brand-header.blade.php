@php
    $isLoginPage = request()->routeIs('filament.*.auth.login');
@endphp

<div class="group flex p-1.5 transition-all duration-300 {{ 
    $isLoginPage 
        ? 'flex-col items-center gap-4 pb-8' 
        : 'items-center gap-3' 
}}">
    <!-- Container Logo dengan Efek Gradient Border & Hover Zoom -->
    <div class="relative shrink-0">
        <div class="absolute -inset-0.5 rounded-xl bg-gradient-to-r from-primary-500 to-amber-500 opacity-30 blur-[2px] transition duration-300 group-hover:opacity-75"></div>
        <img 
            src="{{ $logoUrl }}" 
            alt="Logo {{ $brandName }}" 
            class="relative rounded-lg bg-white object-cover shadow-md transition-transform duration-300 group-hover:scale-105 dark:bg-gray-800 {{ 
                $isLoginPage ? 'h-20 w-20' : 'h-10 w-10' 
            }}"
        />
    </div>

    <!-- Info Teks — disembunyikan seluruhnya di halaman login -->
    @unless ($isLoginPage)
        <div class="flex min-w-0 flex-col justify-center gap-0">
            <!-- Baris Atas: Nama Tenant / Brand -->
            <span class="truncate text-sm font-extrabold tracking-tight leading-snug text-gray-900 transition-colors duration-200 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                {{ $brandName }}
            </span>

            <!-- Baris Bawah: Nama Aplikasi -->
            <span class="truncate text-[11px] font-medium uppercase tracking-wider leading-snug text-gray-500 dark:text-gray-400">
                {{ $appName }}
            </span>
        </div>
    @endunless
</div>
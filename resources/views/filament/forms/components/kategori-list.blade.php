{{-- resources/views/filament/forms/components/kategori-list.blade.php --}}
<div class="flex h-full flex-col rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
    <div class="mb-3 flex items-center gap-2">
        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
        </svg>
        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
            Kategori Tersedia
        </h3>
    </div>

    <p class="mb-3 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
        Isi kolom <code class="rounded bg-gray-200 px-1 py-0.5 font-mono text-[11px] text-gray-700 dark:bg-white/10 dark:text-gray-200">kategori</code>
        pada file Excel/CSV Anda persis dengan salah satu nama di bawah ini.
    </p>

    <div class="flex-1 space-y-1.5 overflow-y-auto pr-1" style="max-height: 19rem;">
        @forelse ($kategoris as $kategori)
            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary-500"></span>
                <span class="truncate">{{ $kategori }}</span>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-gray-300 px-3 py-4 text-center text-xs italic text-gray-400 dark:border-white/10">
                Belum ada kategori. Tambahkan kategori terlebih dahulu sebelum import.
            </div>
        @endforelse
    </div>

    <div class="mt-3 flex items-center gap-1.5 border-t border-gray-200 pt-3 text-[11px] text-gray-400 dark:border-white/10">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
       <p class="mb-3 text-xs leading-relaxed text-gray-500 dark:text-gray-400"> Nama kategori tidak peka huruf besar/kecil (case-insensitive).</p>
    </div>
</div>
<x-filament-panels::page>
    <style>
        .kt-root {
            --kt-surface: #ffffff;
            --kt-border: #e5e7eb;
            --kt-text: #111827;
            --kt-text-2: #374151;
            --kt-muted: #6b7280;
            --kt-head-bg: #f9fafb;
            --kt-hover: #f9fafb;
            --kt-input-bg: #ffffff;
            --kt-input-border: #d1d5db;
            --kt-overlay: rgba(3, 7, 18, .5);
            --kt-primary: rgb(var(--primary-600, 79, 70, 229));
            --kt-primary-hover: rgb(var(--primary-500, 99, 102, 241));
            --kt-primary-ring: rgba(var(--primary-500, 99, 102, 241), .3);
            --kt-danger: #dc2626;
            --kt-danger-hover: #ef4444;
            --kt-danger-soft: #fee2e2;
            --kt-warning: #b45309;
        }

        .dark .kt-root {
            --kt-surface: #111827;
            --kt-border: rgba(255, 255, 255, .1);
            --kt-text: #ffffff;
            --kt-text-2: #d1d5db;
            --kt-muted: #9ca3af;
            --kt-head-bg: rgba(255, 255, 255, .05);
            --kt-hover: rgba(255, 255, 255, .05);
            --kt-input-bg: rgba(255, 255, 255, .05);
            --kt-input-border: rgba(255, 255, 255, .1);
            --kt-overlay: rgba(0, 0, 0, .6);
            --kt-danger-soft: rgba(239, 68, 68, .15);
            --kt-warning: #fbbf24;
        }

        /* ---------- Card ---------- */
        .kt-card {
            background: var(--kt-surface);
            border: 1px solid var(--kt-border);
            border-radius: .75rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
            overflow: hidden;
        }

        .kt-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid var(--kt-border);
        }

        .kt-search {
            position: relative;
            flex: 1 1 240px;
            max-width: 320px;
        }

        .kt-search .kt-icon {
            position: absolute;
            left: .75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--kt-muted);
            pointer-events: none;
        }

        .kt-search input { padding-left: 2.5rem; }

        /* ---------- Icon ---------- */
        .kt-icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
        .kt-icon-sm { width: 1rem; height: 1rem; flex-shrink: 0; }
        .kt-icon-lg { width: 2.5rem; height: 2.5rem; }

        /* ---------- Input ---------- */
        .kt-input {
            width: 100%;
            box-sizing: border-box;
            padding: .5rem .75rem;
            font-size: .875rem;
            line-height: 1.25rem;
            color: var(--kt-text);
            background: var(--kt-input-bg);
            border: 1px solid var(--kt-input-border);
            border-radius: .5rem;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .kt-input::placeholder { color: var(--kt-muted); }

        .kt-input:focus {
            border-color: var(--kt-primary);
            box-shadow: 0 0 0 3px var(--kt-primary-ring);
        }

        .kt-input.kt-invalid { border-color: var(--kt-danger); }

        .kt-label {
            display: block;
            margin-bottom: .25rem;
            font-size: .875rem;
            font-weight: 500;
            color: var(--kt-text-2);
        }

        .kt-required { color: var(--kt-danger); }

        .kt-error {
            margin: .25rem 0 0;
            font-size: .75rem;
            color: var(--kt-danger);
        }

        /* ---------- Button ---------- */
        .kt-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            padding: .5rem 1rem;
            font-size: .875rem;
            font-weight: 600;
            line-height: 1.25rem;
            border: 1px solid transparent;
            border-radius: .5rem;
            cursor: pointer;
            transition: background .15s, opacity .15s;
            background: transparent;
        }

        .kt-btn:disabled { opacity: .6; cursor: not-allowed; }

        .kt-btn-primary { color: #fff; background: var(--kt-primary); }
        .kt-btn-primary:hover:not(:disabled) { background: var(--kt-primary-hover); }

        .kt-btn-danger { color: #fff; background: var(--kt-danger); }
        .kt-btn-danger:hover:not(:disabled) { background: var(--kt-danger-hover); }

        .kt-btn-outline {
            color: var(--kt-text-2);
            font-weight: 500;
            border-color: var(--kt-input-border);
        }
        .kt-btn-outline:hover { background: var(--kt-hover); }

        .kt-btn-sm { padding: .3rem .65rem; font-size: .75rem; font-weight: 500; }

        .kt-btn-edit { color: var(--kt-warning); border-color: currentColor; }
        .kt-btn-edit:hover { background: rgba(245, 158, 11, .1); }

        .kt-btn-delete { color: var(--kt-danger); border-color: currentColor; }
        .kt-btn-delete:hover { background: var(--kt-danger-soft); }

        .kt-icon-btn {
            display: inline-flex;
            padding: .25rem;
            color: var(--kt-muted);
            background: transparent;
            border: 0;
            border-radius: .5rem;
            cursor: pointer;
        }
        .kt-icon-btn:hover { background: var(--kt-hover); color: var(--kt-text); }

        /* ---------- Table ---------- */
        .kt-table-wrap { overflow-x: auto; }

        .kt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
            text-align: left;
        }

        .kt-table thead { background: var(--kt-head-bg); }

        .kt-table th {
            padding: .75rem 1rem;
            font-weight: 600;
            color: var(--kt-text-2);
            white-space: nowrap;
        }

        .kt-table td {
            padding: .75rem 1rem;
            color: var(--kt-text-2);
            border-top: 1px solid var(--kt-border);
        }

        .kt-table tbody tr:hover { background: var(--kt-hover); }

        .kt-col-no { width: 4rem; }
        .kt-right { text-align: right !important; }
        .kt-strong { font-weight: 600; color: var(--kt-text) !important; }
        .kt-muted { color: var(--kt-muted) !important; }

        .kt-sort {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: 0;
            font: inherit;
            font-weight: 600;
            color: inherit;
            background: none;
            border: 0;
            cursor: pointer;
        }
        .kt-sort:hover { color: var(--kt-text); }
        .kt-sort small { font-size: .65rem; }

        .kt-actions { display: flex; gap: .5rem; justify-content: flex-end; }

        .kt-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
            padding: 3rem 1rem;
            color: var(--kt-muted);
            text-align: center;
        }
        .kt-empty p { margin: 0; }
        .kt-empty .kt-empty-title { font-size: .875rem; font-weight: 500; }
        .kt-empty .kt-empty-desc { font-size: .75rem; }

        .kt-footer {
            padding: 1rem;
            border-top: 1px solid var(--kt-border);
        }

        /* ---------- Modal ---------- */
        .kt-modal {
            position: fixed;
            inset: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .kt-backdrop {
            position: absolute;
            inset: 0;
            background: var(--kt-overlay);
            backdrop-filter: blur(2px);
            animation: kt-fade .15s ease-out;
        }

        .kt-dialog {
            position: relative;
            width: 100%;
            max-width: 28rem;
            max-height: 90vh;
            overflow-y: auto;
            background: var(--kt-surface);
            border: 1px solid var(--kt-border);
            border-radius: .75rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, .2);
            animation: kt-pop .18s ease-out;
        }

        .kt-dialog-sm { max-width: 24rem; text-align: center; padding: 1.5rem; }

        .kt-dialog-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--kt-border);
        }

        .kt-dialog-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--kt-text);
        }

        .kt-dialog-body { padding: 1.25rem 1.5rem; display: grid; gap: 1rem; }

        .kt-dialog-foot {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--kt-border);
        }

        .kt-warn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            color: var(--kt-danger);
            background: var(--kt-danger-soft);
            border-radius: 9999px;
        }

        .kt-confirm-text { margin: .25rem 0 0; font-size: .875rem; color: var(--kt-muted); }
        .kt-confirm-foot { display: flex; justify-content: center; gap: .5rem; margin-top: 1.5rem; }

        @keyframes kt-fade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes kt-pop {
            from { opacity: 0; transform: translateY(8px) scale(.98); }
            to   { opacity: 1; transform: none; }
        }
    </style>

    <div class="kt-root">

        {{-- ========== LIST ========== --}}
        <div class="kt-card">

            {{-- Toolbar --}}
            <div class="kt-toolbar">
                <div class="kt-search">
                    <x-heroicon-o-magnifying-glass class="kt-icon" />
                    <input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari kategori..."
                        class="kt-input"
                    >
                </div>

                <button type="button" wire:click="openCreate" class="kt-btn kt-btn-primary">
                    <x-heroicon-m-plus class="kt-icon" />
                    Tambah Kategori
                </button>
            </div>

            {{-- Table --}}
            <div class="kt-table-wrap">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="kt-col-no">No</th>

                            @if ($isSuperAdmin)
                                <th>Toko</th>
                            @endif

                            <th>
                                <button type="button" wire:click="sortBy('nama_kategori')" class="kt-sort">
                                    Nama Kategori
                                    @if ($sortField === 'nama_kategori')
                                        <small>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</small>
                                    @endif
                                </button>
                            </th>

                            <th>
                                <button type="button" wire:click="sortBy('created_at')" class="kt-sort">
                                    Dibuat
                                    @if ($sortField === 'created_at')
                                        <small>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</small>
                                    @endif
                                </button>
                            </th>

                            <th class="kt-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($kategoris as $kategori)
                            <tr wire:key="kategori-{{ $kategori->id }}">
                                <td class="kt-muted">
                                    {{ $kategori->created_at ? \Illuminate\Support\Carbon::parse($kategori->created_at)->format('d M Y') : '-' }}
                                </td>
                                @if ($isSuperAdmin)
                                    <td>{{ $kategori->tenant?->nama ?? '-' }}</td>
                                @endif

                                <td class="kt-strong">{{ $kategori->nama_kategori }}</td>

                                <td class="kt-muted">{{ $kategori->created_at?->format('d M Y') }}</td>

                                <td>
                                    <div class="kt-actions">
                                        <button
                                            type="button"
                                            wire:click="openEdit({{ $kategori->id }})"
                                            class="kt-btn kt-btn-sm kt-btn-edit"
                                        >
                                            <x-heroicon-m-pencil-square class="kt-icon-sm" />
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $kategori->id }})"
                                            class="kt-btn kt-btn-sm kt-btn-delete"
                                        >
                                            <x-heroicon-m-trash class="kt-icon-sm" />
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 5 : 4 }}">
                                    <div class="kt-empty">
                                        <x-heroicon-o-tag class="kt-icon-lg" />
                                        <p class="kt-empty-title">Belum ada kategori</p>
                                        <p class="kt-empty-desc">Klik "Tambah Kategori" untuk membuat yang pertama.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination (komponen bawaan Filament, sudah ter-style + ada pilihan jumlah per halaman) --}}
            <div class="kt-footer">
                <x-filament::pagination
                    :paginator="$kategoris"
                    :page-options="[10, 25, 50]"
                    current-page-option-property="perPage"
                />
            </div>
        </div>

        {{-- ========== MODAL CREATE / EDIT ========== --}}
        @if ($showModal)
            <div class="kt-modal" x-data x-on:keydown.escape.window="$wire.closeModal()">
                <div class="kt-backdrop" wire:click="closeModal"></div>

                <div class="kt-dialog">
                    <div class="kt-dialog-head">
                        <h3 class="kt-dialog-title">
                            {{ $editingId ? 'Edit Kategori' : 'Tambah Kategori' }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="kt-icon-btn" aria-label="Tutup">
                            <x-heroicon-m-x-mark class="kt-icon" />
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="kt-dialog-body">
                            @if ($isSuperAdmin)
                                <div>
                                    <label class="kt-label">Toko <span class="kt-required">*</span></label>
                                    <select
                                        wire:model="tenant_id"
                                        class="kt-input @error('tenant_id') kt-invalid @enderror"
                                    >
                                        <option value="">-- Pilih Toko --</option>
                                        @foreach ($tenants as $tenant)
                                            <option value="{{ $tenant->id }}">{{ $tenant->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('tenant_id')
                                        <p class="kt-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <div>
                                <label class="kt-label">Nama Kategori <span class="kt-required">*</span></label>
                                <input
                                    type="text"
                                    wire:model="nama_kategori"
                                    maxlength="100"
                                    placeholder="Contoh: Minuman"
                                    x-init="$nextTick(() => $el.focus())"
                                    class="kt-input @error('nama_kategori') kt-invalid @enderror"
                                >
                                @error('nama_kategori')
                                    <p class="kt-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="kt-dialog-foot">
                            <button type="button" wire:click="closeModal" class="kt-btn kt-btn-outline">
                                Batal
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="kt-btn kt-btn-primary"
                            >
                                <span wire:loading wire:target="save">Menyimpan...</span>
                                <span wire:loading.remove wire:target="save">Simpan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ========== MODAL KONFIRMASI HAPUS ========== --}}
        @if ($showDeleteModal)
            <div class="kt-modal" x-data x-on:keydown.escape.window="$wire.cancelDelete()">
                <div class="kt-backdrop" wire:click="cancelDelete"></div>

                <div class="kt-dialog kt-dialog-sm">
                    <div class="kt-warn-icon">
                        <x-heroicon-o-exclamation-triangle class="kt-icon" />
                    </div>
                    <h3 class="kt-dialog-title">Hapus kategori?</h3>
                    <p class="kt-confirm-text">Data yang dihapus tidak dapat dikembalikan.</p>

                    <div class="kt-confirm-foot">
                        <button type="button" wire:click="cancelDelete" class="kt-btn kt-btn-outline">
                            Batal
                        </button>
                        <button type="button" wire:click="delete" class="kt-btn kt-btn-danger">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
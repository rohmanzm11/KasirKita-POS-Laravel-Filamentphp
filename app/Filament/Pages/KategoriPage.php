<?php

namespace App\Filament\Pages;

use App\Models\Kategori;
use App\Models\Tenant;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithPagination;

class KategoriPage extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    // protected static ?string $navigationGroup = 'Tenant';
    protected static ?string $navigationLabel = 'Kategori';
    protected static ?string $title           = 'Kategori';
    protected static ?string $slug            = 'kategori';
    protected static ?int    $navigationSort  = 3;

    protected static string $view = 'filament.pages.kategori';

    // List state
    public string $search        = '';
    public string $sortField     = 'created_at';
    public string $sortDirection = 'desc';
    public int    $perPage       = 10;

    // Modal state
    public bool  $showModal       = false;
    public bool  $showDeleteModal = false;
    public ?int  $editingId       = null;
    public ?int  $deletingId      = null;

    // Form state
    public ?int   $tenant_id     = null;
    public string $nama_kategori = '';

//     public static function canViewAny(): bool
// {
//     /** @var \App\Models\User|null $user */
//     $user = auth()->user();

//     return $user?->isSuperAdmin() || $user?->isOwner();
// }
    protected function baseQuery()
    {
        $query = Kategori::query();

        // Non-superadmin hanya boleh melihat kategori tokonya sendiri
        if (! static::isSuperAdmin()) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        return $query;
    }

    protected function getViewData(): array
    {
        $kategoris = $this->baseQuery()
            ->with('tenant')
            ->when($this->search, fn ($q) => $q->where('nama_kategori', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'kategoris'    => $kategoris,
            'tenants'      => $this->isSuperAdmin() ? Tenant::orderBy('nama')->get() : collect(),
            'isSuperAdmin' => $this->isSuperAdmin(),
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ---------- Create / Edit ----------

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $kategori = $this->baseQuery()->findOrFail($id);

        $this->resetValidation();
        $this->editingId     = $kategori->id;
        $this->tenant_id     = $kategori->tenant_id;
        $this->nama_kategori = $kategori->nama_kategori;
        $this->showModal     = true;
    }

    public function save(): void
    {
        $rules = [
            'nama_kategori' => ['required', 'string', 'max:100'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['tenant_id'] = ['required', 'exists:tenants,id'];
        }

        $this->validate($rules, [], [
            'nama_kategori' => 'nama kategori',
            'tenant_id'     => 'toko',
        ]);

        $data = ['nama_kategori' => $this->nama_kategori];

        $data['tenant_id'] = $this->isSuperAdmin()
            ? $this->tenant_id
            : auth()->user()->tenant_id;

        if ($this->editingId) {
            $this->baseQuery()->findOrFail($this->editingId)->update($data);
            $message = 'Kategori berhasil diperbarui';
        } else {
            Kategori::create($data);
            $message = 'Kategori berhasil ditambahkan';
        }

        $this->closeModal();

        Notification::make()->title($message)->success()->send();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'tenant_id', 'nama_kategori']);
        $this->resetValidation();
    }

    // ---------- Delete ----------

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $this->baseQuery()->findOrFail($this->deletingId)->delete();

            Notification::make()->title('Kategori berhasil dihapus')->success()->send();
        }

        $this->cancelDelete();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }
}
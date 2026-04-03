<?php

namespace App\Livewire\Admin;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AdmKelas extends Component
{
    use WithPagination;

    public $search = '';

    public $showForm = false;

    public $showDeleteConfirm = false;

    public $editingId = null;

    public $name = '';

    public $wali_kelas_id = '';

    public $guruList = [];

    public function mount()
    {
        $this->guruList = User::where('role', 'Guru')->orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function toggleForm($id = null)
    {
        $this->resetForm();
        $this->showForm = ! $this->showForm;
        if ($id) {
            $kelas = Kelas::findOrFail($id);
            $this->editingId = $id;
            $this->name = $kelas->name;
            $this->wali_kelas_id = (string) ($kelas->wali_kelas_id ?? '');
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:kelas,name,'.($this->editingId ?? 'NULL'),
            'wali_kelas_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'Guru')),
                Rule::unique('kelas', 'wali_kelas_id')->ignore($this->editingId),
            ],
        ]);

        $payload = [
            'name' => $this->name,
            'wali_kelas_id' => filled($this->wali_kelas_id) ? $this->wali_kelas_id : null,
        ];

        if ($this->editingId) {
            $kelas = Kelas::findOrFail($this->editingId);
            $kelas->update($payload);
        } else {
            Kelas::create($payload);
        }

        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('swal-success', ['message' => $this->editingId ? 'Kelas berhasil diupdate!' : 'Kelas berhasil ditambahkan!']);
    }

    public function deleteConfirm($id)
    {
        $kelas = Kelas::withCount('siswa')->find($id);
        if ($kelas->siswa_count > 0) {
            $this->dispatch('swal-error', ['message' => 'Tidak bisa hapus kelas yang masih punya siswa! ('.$kelas->siswa_count.' siswa)']);

            return;
        }
        $this->showDeleteConfirm = true;
        $this->editingId = $id;
    }

    public function delete()
    {
        Kelas::findOrFail($this->editingId)->delete();
        $this->resetForm();
        $this->showDeleteConfirm = false;
        $this->dispatch('swal-success', ['message' => 'Kelas berhasil dihapus!']);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->wali_kelas_id = '';
        $this->resetValidation();
        $this->resetPage();
    }

    public function getKelasProperty()
    {
        return Kelas::with(['waliKelas:id,name'])
            ->withCount('siswa')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.adm-kelas', [
            'kelas' => $this->kelas,
        ]);
    }
}

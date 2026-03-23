<?php

namespace App\Livewire\Admin;

use App\Models\Kelas;
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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function toggleForm($id = null)
    {
        $this->resetForm();
        $this->showForm = !$this->showForm;
        if ($id) {
            $kelas = Kelas::find($id);
            $this->editingId = $id;
            $this->name = $kelas->name;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:kelas,name,' . ($this->editingId ?? 'NULL'),
        ]);

        if ($this->editingId) {
            $kelas = Kelas::find($this->editingId);
            $kelas->update(['name' => $this->name]);
        } else {
            Kelas::create(['name' => $this->name]);
        }

        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('swal-success', ['message' => $this->editingId ? 'Kelas berhasil diupdate!' : 'Kelas berhasil ditambahkan!']);
    }

    public function deleteConfirm($id)
    {
        $kelas = Kelas::withCount('siswa')->find($id);
        if ($kelas->siswa_count > 0) {
            $this->dispatch('swal-error', ['message' => 'Tidak bisa hapus kelas yang masih punya siswa! (' . $kelas->siswa_count . ' siswa)']);
            return;
        }
        $this->showDeleteConfirm = true;
        $this->editingId = $id;
    }

    public function delete()
    {
        Kelas::find($this->editingId)->delete();
        $this->resetForm();
        $this->showDeleteConfirm = false;
        $this->dispatch('swal-success', ['message' => 'Kelas berhasil dihapus!']);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->resetValidation();
        $this->resetPage();
    }

    public function getKelasProperty()
    {
        return Kelas::withCount('siswa')
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

<?php

namespace App\Livewire\Admin;

use App\Models\Siswa;
use App\Models\Kelas;
use Livewire\Component;
use Livewire\WithPagination;

class AdmSiswa extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedKelas = '';
    public $kelasList = [];
    public $showForm = false;
    public $showDeleteConfirm = false;
    public $editingId = null;
    public $name = '';
    public $nis = '';
    public $kelas_id = '';

    public function mount()
    {
        $this->kelasList = Kelas::pluck('name', 'id')->toArray();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedKelas()
    {
        $this->resetPage();
    }

    public function toggleForm($id = null)
    {
        $this->resetForm();
        $this->showForm = !$this->showForm;
        if ($id) {
            $siswa = Siswa::find($id);
            $this->editingId = $id;
            $this->name = $siswa->name;
            $this->nis = $siswa->nis;
            $this->kelas_id = $siswa->kelas_id;
        }
    }

    public function save()
    {
        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'nis' => 'required|string|size:5|unique:siswa,nis,' . ($this->editingId ?? 'NULL'),
                'kelas_id' => 'required|exists:kelas,id',
            ]);

            if ($this->editingId) {
                $siswa = Siswa::find($this->editingId);
                $siswa->update([
                    'name' => $this->name,
                    'nis' => $this->nis,
                    'kelas_id' => $this->kelas_id,
                ]);
            } else {
                Siswa::create([
                    'name' => $this->name,
                    'nis' => $this->nis,
                    'kelas_id' => $this->kelas_id,
                ]);
            }

            $this->resetForm();
            $this->showForm = false;
            // $this->dispatch('swal-success', ['message' => $this->editingId ? 'Siswa berhasil diupdate!' : 'Siswa berhasil ditambahkan!']);
        } catch (\Exception $e) {
            \Log::error('Error saving siswa: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteConfirm($id)
    {
        $this->showDeleteConfirm = true;
        $this->editingId = $id;
    }

    public function delete()
    {
        Siswa::find($this->editingId)->delete();
        $this->resetForm();
        $this->showDeleteConfirm = false;
        session()->flash('message', 'Siswa berhasil dihapus!');
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->nis = '';
        $this->kelas_id = '';
        $this->resetValidation();
        $this->resetPage();
    }

    public function getSiswaProperty()
    {
        return Siswa::with('kelas')
            ->search($this->search)
            ->kelas($this->selectedKelas)
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.adm-siswa', [
            'siswa' => $this->siswa
        ]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AdmMapel extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedKelas = '';
    public $showForm = false;
    public $showDeleteConfirm = false;
    public $editingId = null;

    public $kode_mapel = '';
    public $nama_mapel = '';
    public $kelas_id = '';
    public $guru_id = '';

    public $kelasList = [];
    public $guruList = [];

    public function mount()
    {
        $this->kelasList = Kelas::orderBy('name')->pluck('name', 'id')->toArray();
        $this->guruList = User::where('role', 'Guru')->orderBy('name')->pluck('name', 'id')->toArray();
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
            $mapel = Mapel::with('gurus:id,name')->findOrFail($id);

            $this->editingId = $id;
            $this->kode_mapel = $mapel->kode_mapel;
            $this->nama_mapel = $mapel->nama_mapel;
            $this->kelas_id = (string) $mapel->kelas_id;
            $this->guru_id = (string) ($mapel->gurus->first()?->id ?? '');
        }
    }

    public function save()
    {
        $this->validate([
            'kode_mapel' => ['required', 'string', 'max:50', Rule::unique('mapels', 'kode_mapel')->ignore($this->editingId)],
            'nama_mapel' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn($query) => $query->where('role', 'Guru')),
            ],
        ]);

        $payload = [
            'kode_mapel' => $this->kode_mapel,
            'nama_mapel' => $this->nama_mapel,
            'kelas_id' => $this->kelas_id,
        ];

        $isEditing = (bool) $this->editingId;

        if ($isEditing) {
            $mapel = Mapel::findOrFail($this->editingId);
            $mapel->update($payload);
        } else {
            $mapel = Mapel::create($payload);
        }

        $mapel->gurus()->sync([$this->guru_id]);

        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('swal-success', ['message' => $isEditing ? 'Mata pelajaran berhasil diupdate!' : 'Mata pelajaran berhasil ditambahkan!']);
    }

    public function deleteConfirm($id)
    {
        $this->showDeleteConfirm = true;
        $this->editingId = $id;
    }

    public function delete()
    {
        $mapel = Mapel::findOrFail($this->editingId);
        $mapel->delete();

        $this->resetForm();
        $this->showDeleteConfirm = false;
        $this->dispatch('swal-success', ['message' => 'Mata pelajaran berhasil dihapus!']);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->kode_mapel = '';
        $this->nama_mapel = '';
        $this->kelas_id = '';
        $this->guru_id = '';
        $this->resetValidation();
        $this->resetPage();
    }

    public function getMapelsProperty()
    {
        return Mapel::with(['kelas', 'gurus'])
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('kode_mapel', 'like', "%{$this->search}%")
                        ->orWhere('nama_mapel', 'like', "%{$this->search}%");
                });
            })
            ->when($this->selectedKelas, function ($query) {
                $query->where('kelas_id', $this->selectedKelas);
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.adm-mapel', [
            'mapels' => $this->mapels,
        ]);
    }
}

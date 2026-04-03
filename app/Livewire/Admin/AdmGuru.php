<?php

namespace App\Livewire\Admin;

use App\Models\User;
// if needed
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class AdmGuru extends Component
{
    use WithPagination;

    public $search = '';

    public $showForm = false;

    public $showDeleteConfirm = false;

    public $editingId = null;

    public $name = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public function mount()
    {
        // No kelasList for guru
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
            $guru = User::find($id);
            $this->editingId = $id;
            $this->name = $guru->name;
            $this->email = $guru->email;
            // password not loaded
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.($this->editingId ?? 'NULL'),
        ];

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => 'Guru',
        ];

        if (! $this->editingId) {
            $rules['password'] = 'required|confirmed|min:8';
            $data['password'] = Hash::make($this->password);
        } elseif ($this->password) {
            $rules['password'] = 'confirmed|min:8';
            $data['password'] = Hash::make($this->password);
        }

        $this->validate($rules);

        if ($this->editingId) {
            $guru = User::find($this->editingId);
            $guru->update($data);
        } else {
            User::create($data);
        }

        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('swal-success', ['message' => $this->editingId ? 'Guru berhasil diupdate!' : 'Guru berhasil ditambahkan!']);
    }

    public function deleteConfirm($id)
    {
        if (auth()->id() == $id) {
            $this->dispatch('swal-error', ['message' => 'Tidak bisa hapus akun sendiri!']);

            return;
        }
        $this->showDeleteConfirm = true;
        $this->editingId = $id;
    }

    public function delete()
    {
        User::find($this->editingId)->delete();
        $this->resetForm();
        $this->showDeleteConfirm = false;
        $this->dispatch('swal-success', ['message' => 'Guru berhasil dihapus!']);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
        $this->resetPage();
    }

    public function getGuruProperty()
    {
        return User::where('role', 'Guru')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.adm-guru', [
            'guru' => $this->guru,
        ]);
    }
}

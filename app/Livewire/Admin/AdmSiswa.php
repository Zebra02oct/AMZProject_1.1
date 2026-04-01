<?php

namespace App\Livewire\Admin;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
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
    public $phone = '';
    public $address = '';
    public $password = '';
    public $password_confirmation = '';

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
            $siswa = Siswa::findOrFail($id);
            $this->editingId = $id;
            $this->name = $siswa->name;
            $this->nis = $siswa->nis;
            $this->kelas_id = $siswa->kelas_id;
            $this->phone = $siswa->phone ?? '';
            $this->address = $siswa->address ?? '';
        }
    }

    public function save()
    {
        try {
            $linkedUserId = null;

            if ($this->editingId) {
                $linkedUserId = Siswa::whereKey($this->editingId)->value('user_id');
            }

            $this->validate([
                'name' => 'required|string|max:255',
                'nis' => ['required', 'string', 'size:5', Rule::unique('siswa', 'nis')->ignore($this->editingId)],
                'kelas_id' => 'required|exists:kelas,id',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:1000',
                'password' => $this->editingId ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
            ]);

            validator(
                ['email' => $this->buildSiswaEmail($this->nis)],
                ['email' => [Rule::unique('users', 'email')->ignore($linkedUserId)]]
            )->validate();

            if (Schema::hasColumn('users', 'nis')) {
                validator(
                    ['nis' => $this->nis],
                    ['nis' => [Rule::unique('users', 'nis')->ignore($linkedUserId)]]
                )->validate();
            }

            $isEditing = (bool) $this->editingId;

            DB::transaction(function () {
                if ($this->editingId) {
                    $siswa = Siswa::with('user')->findOrFail($this->editingId);
                    $user = $siswa->user;

                    if (!$user) {
                        $user = User::create($this->buildSiswaUserPayload(true, '123456'));
                    } else {
                        $user->update($this->buildSiswaUserPayload(filled($this->password)));
                    }

                    $siswa->update($this->buildSiswaPayload($user->id));
                } else {
                    $user = User::create($this->buildSiswaUserPayload());

                    Siswa::create($this->buildSiswaPayload($user->id));
                }
            });

            $this->resetForm();
            $this->showForm = false;
            $this->dispatch('swal-success', ['message' => $isEditing ? 'Siswa berhasil diupdate!' : 'Siswa berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error saving siswa: ' . $e->getMessage());
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
        DB::transaction(function () {
            $siswa = Siswa::with('user')->findOrFail($this->editingId);
            $user = $siswa->user;

            $siswa->delete();

            if ($user) {
                $user->delete();
            }
        });

        $this->resetForm();
        $this->showDeleteConfirm = false;
        $this->dispatch('swal-success', ['message' => 'Siswa berhasil dihapus!']);
    }

    private function buildSiswaUserPayload(bool $withPassword = true, ?string $fallbackPassword = null): array
    {
        $payload = [
            'name' => $this->name,
            'email' => $this->buildSiswaEmail($this->nis),
            'role' => 'Siswa',
        ];

        if (Schema::hasColumn('users', 'nis')) {
            $payload['nis'] = $this->nis;
        }

        if ($withPassword) {
            $rawPassword = $this->password ?: $fallbackPassword;

            if ($rawPassword) {
                $payload['password'] = Hash::make($rawPassword);
            }
        }

        return $payload;
    }

    private function buildSiswaEmail(string $nis): string
    {
        return $nis . '@siswa.local';
    }

    private function buildSiswaPayload(int $userId): array
    {
        $payload = [
            'name' => $this->name,
            'nis' => $this->nis,
            'kelas_id' => $this->kelas_id,
            'user_id' => $userId,
        ];

        // Jaga kompatibilitas ketika migration phone/address belum dijalankan.
        if (Schema::hasColumn('siswa', 'phone')) {
            $payload['phone'] = filled($this->phone) ? $this->phone : null;
        }

        if (Schema::hasColumn('siswa', 'address')) {
            $payload['address'] = filled($this->address) ? $this->address : null;
        }

        return $payload;
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->nis = '';
        $this->kelas_id = '';
        $this->phone = '';
        $this->address = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
        $this->resetPage();
    }

    public function getSiswaProperty()
    {
        return Siswa::with(['kelas', 'user'])
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

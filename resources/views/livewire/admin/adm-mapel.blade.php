<div class="space-y-6" x-data="mapelComponent()">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[#7a4f16] dark:text-[#ffd889]">Manajemen Mata Pelajaran</h1>
            <p class="text-[#8b6a3c] dark:text-[#e5c58d] mt-1">Kelola mapel per kelas dan guru pengampu</p>
        </div>
        <button wire:click="toggleForm"
            class="bg-[#8f4f11] hover:bg-[#7b430e] text-[#fff8ec] px-6 py-2 rounded-lg font-medium shadow-lg transition-all duration-200 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Mapel
        </button>
    </div>

    <div class="bg-white/95 dark:bg-[#3a2a13] rounded-xl shadow-sm border border-[#ecd6aa] dark:border-[#8d662b] p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari mapel</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode atau nama mapel..."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#b97820] focus:border-[#b97820] dark:bg-gray-700 dark:text-white transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter kelas</label>
                <select wire:model.live="selectedKelas"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#b97820] focus:border-[#b97820] dark:bg-gray-700 dark:text-white transition-all">
                    <option value="">Semua kelas</option>
                    @foreach ($kelasList as $kelasId => $kelasName)
                        <option value="{{ $kelasId }}">{{ $kelasName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div
        class="bg-white/95 dark:bg-[#3a2a13] rounded-xl shadow-sm border border-[#ecd6aa] dark:border-[#8d662b] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#f8e9c8] dark:bg-[#4a3618]">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            No</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Kode</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Mata Pelajaran</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Kelas</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Guru</th>
                        <th
                            class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($mapels as $index => $mapel)
                        <tr class="hover:bg-[#fff3dc] dark:hover:bg-[#4a3618] transition-colors">
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#7a4f16] dark:text-[#ffd889]">
                                {{ ($mapels->currentPage() - 1) * $mapels->perPage() + $index + 1 }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#7a4f16] dark:text-[#ffd889]">
                                {{ $mapel->kode_mapel }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#7a4f16] dark:text-[#ffd889]">
                                {{ $mapel->nama_mapel }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#7a4f16] dark:text-[#ffd889]">
                                <span
                                    class="px-3 py-1 bg-[#f7e5c0] text-[#8f4f11] dark:bg-[#5a401a] dark:text-[#f0c66f] text-xs font-medium rounded-full">
                                    {{ $mapel->kelas?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#7a4f16] dark:text-[#ffd889]">
                                {{ $mapel->gurus->pluck('name')->join(', ') ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-weight-500 space-x-2">
                                <button wire:click="toggleForm({{ $mapel->id }})"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium shadow-md transition-all duration-200"
                                    title="Edit">
                                    ✏️ Edit
                                </button>
                                <button wire:click="deleteConfirm({{ $mapel->id }})"
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium shadow-md transition-all duration-200"
                                    title="Hapus">
                                    🗑️ Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[#8b6a3c] dark:text-[#e5c58d]">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h8l6 6v10a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p>Belum ada data mata pelajaran</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($mapels->hasPages())
            <div class="px-6 py-4 bg-[#f8e9c8] dark:bg-[#4a3618] border-t border-gray-200 dark:border-gray-600">
                {{ $mapels->links() }}
            </div>
        @endif
    </div>

    <div x-show="showFormModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
        style="display: none;" @keydown.escape.window="closeFormModal()" @swal-closed.window="showFormModal = false">
        <div class="bg-white/95 dark:bg-[#3a2a13] rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-[#7a4f16] dark:text-[#ffd889]">
                    {{ $editingId ? 'Edit Mapel' : 'Tambah Mapel Baru' }}
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kode
                            Mapel</label>
                        <input type="text" wire:model="kode_mapel" placeholder="Contoh: MTL001"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#b97820] focus:border-[#b97820] dark:bg-gray-700 dark:text-white transition-all @error('kode_mapel') ring-2 ring-red-200 @enderror">
                        @error('kode_mapel')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama
                            Mapel</label>
                        <input type="text" wire:model="nama_mapel" placeholder="Contoh: Matematika"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#b97820] focus:border-[#b97820] dark:bg-gray-700 dark:text-white transition-all @error('nama_mapel') ring-2 ring-red-200 @enderror">
                        @error('nama_mapel')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kelas</label>
                        <select wire:model="kelas_id"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#b97820] focus:border-[#b97820] dark:bg-gray-700 dark:text-white transition-all @error('kelas_id') ring-2 ring-red-200 @enderror">
                            <option value="">Pilih kelas</option>
                            @foreach ($kelasList as $kelasId => $kelasName)
                                <option value="{{ $kelasId }}">{{ $kelasName }}</option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Guru
                            Pengampu</label>
                        <select wire:model="guru_id"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#b97820] focus:border-[#b97820] dark:bg-gray-700 dark:text-white transition-all @error('guru_id') ring-2 ring-red-200 @enderror">
                            <option value="">Pilih guru</option>
                            @foreach ($guruList as $guruId => $guruName)
                                <option value="{{ $guruId }}">{{ $guruName }}</option>
                            @endforeach
                        </select>
                        @error('guru_id')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div
                class="px-6 py-4 bg-[#f8e9c8] dark:bg-[#4a3618] border-t border-gray-200 dark:border-gray-600 flex justify-end gap-3">
                <button @click="closeFormModal()"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all font-medium">
                    Batal
                </button>
                <button wire:click="save"
                    class="bg-[#8f4f11] hover:bg-[#7b430e] text-[#fff8ec] px-6 py-2 rounded-lg font-medium shadow-lg transition-all">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
        style="display: none;" @keydown.escape.window="closeDeleteModal()"
        @swal-closed.window="showDeleteModal = false">
        <div class="bg-white/95 dark:bg-[#3a2a13] rounded-2xl shadow-2xl max-w-sm w-full text-center">
            <div class="p-6">
                <div
                    class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/50 mb-4">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#7a4f16] dark:text-[#ffd889] mb-2">Hapus Mapel?</h3>
                <p class="text-[#8b6a3c] dark:text-[#e5c58d] mb-6">Yakin ingin menghapus mapel ini? Aksi ini tidak
                    dapat
                    dibatalkan.</p>
            </div>
            <div class="px-6 pb-6 flex justify-end gap-3">
                <button @click="closeDeleteModal()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all font-medium">
                    Batal
                </button>
                <button wire:click="delete"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium shadow-lg transition-all">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function mapelComponent() {
            return {
                showFormModal: @entangle('showForm'),
                showDeleteModal: @entangle('showDeleteConfirm'),
                init() {
                    this.$wire.on('swal-success', (data) => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    });
                    this.$wire.on('swal-error', (data) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message,
                            timer: 5000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    });
                },
                closeFormModal() {
                    this.showFormModal = false;
                    this.$wire.set('showForm', false);
                },
                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.$wire.set('showDeleteConfirm', false);
                }
            }
        }
    </script>
</div>

<style>
    @layer components {
        [x-cloak] {
            display: none !important;
        }
    }
</style>

<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public bool $isFormOpen = false;
    public bool $isEditing = false;
    public ?int $employeeId = null;

    // --- STATE UNTUK TAB & FILTER ---
    public string $activeTab = 'siswa';
    public string $activeFilter = ''; // State baru untuk menyimpan subfolder (kelas/jabatan) yang diklik
    public string $type = 'siswa';

    public string $name = '';
    public string $employee_code = ''; 
    public string $phone = '';
    public string $position = '';
    public string $status = 'active';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('employees', 'employee_code')->ignore($this->employeeId)
            ],
            'type' => ['required', 'in:siswa,guru,pegawai'],
            'phone' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function switchTab($tab): void
    {
        $this->activeTab = $tab;
        $this->activeFilter = ''; // Reset filter subfolder saat ganti tab utama
        $this->resetPage();
    }

    // --- METHOD BARU UNTUK FILTER SUBFOLDER ---
    public function setFilter($filter): void
    {
        $this->activeFilter = $filter;
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'employee_code', 'phone', 'position', 'status', 'employeeId']);
        $this->type = $this->activeTab;
        
        // Opsional: Otomatis isi kolom form kelas dengan subfolder yang sedang dibuka
        $this->position = $this->activeFilter; 
        
        $this->isEditing = false;
        $this->isFormOpen = true;
    }

    public function edit(Employee $employee): void
    {
        $this->resetValidation();
        $this->employeeId = $employee->id;
        
        $this->name = $employee->user->name ?? ''; 
        $this->employee_code = $employee->employee_code;
        $this->type = $employee->type;
        $this->phone = $employee->phone ?? '';
        $this->position = $employee->position ?? '';
        $this->status = $employee->status;

        $this->isEditing = true;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if (! $this->isEditing) {
            $domain = match($this->type) {
                'guru' => '@guru.local',
                'pegawai' => '@staff.local',
                default => '@student.local',
            };

            $user = User::create([
                'name' => $this->name,
                'email' => $this->employee_code . $domain, 
                'password' => Hash::make('password123'),
            ]);

            Employee::create([
                'user_id' => $user->id,
                'employee_code' => $this->employee_code,
                'type' => $this->type,
                'phone' => $this->phone,
                'position' => $this->position,
                'status' => $this->status,
            ]);
            
        } else {
            $employee = Employee::find($this->employeeId);
            $employee->update([
                'employee_code' => $this->employee_code,
                'type' => $this->type,
                'phone' => $this->phone,
                'position' => $this->position,
                'status' => $this->status,
            ]);

            if ($employee->user) {
                $employee->user->update([
                    'name' => $this->name,
                ]);
            }
        }

        $this->isFormOpen = false;
        $this->reset(['name', 'employee_code', 'phone', 'position', 'status', 'employeeId']);
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    public function with(): array
    {
        // 1. Query Dasar berdasarkan Tab Utama
        $query = Employee::with('user')->where('type', $this->activeTab);

        // 2. Jika ada Subfolder yang diklik, tambahkan filter Where
        if ($this->activeFilter !== '') {
            $query->where('position', $this->activeFilter);
        }

        // 3. Ambil daftar unik dari kolom 'position' untuk dijadikan tombol Subfolder
        $subFilters = Employee::where('type', $this->activeTab)
                        ->whereNotNull('position')
                        ->where('position', '!=', '')
                        ->select('position')
                        ->distinct()
                        ->orderBy('position')
                        ->pluck('position');

        return [
            'employees' => $query->latest()->paginate(10),
            'subFilters' => $subFilters, // Kirim daftar subfolder ke HTML
        ];
    }
};
?>

<!-- ========================================== -->
<!-- BAGIAN HTML / UI -->
<!-- ========================================== -->
<div class="p-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Manajemen Entitas</h2>
            <p class="text-sm text-slate-500">Kelola data murid, guru, dan pegawai sekolah.</p>
        </div>
        <button wire:click="openCreateForm" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah {{ ucfirst($activeTab) }}
        </button>
    </div>

    <!-- Tab Navigasi Utama -->
    <div class="mb-4 border-b border-slate-200">
        <nav class="flex space-x-6" aria-label="Tabs">
            <button wire:click="switchTab('siswa')" class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'siswa' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                Data Siswa
            </button>
            <button wire:click="switchTab('guru')" class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'guru' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                Data Guru
            </button>
            <button wire:click="switchTab('pegawai')" class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'pegawai' ? 'border-teal-500 text-teal-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                Data Pegawai
            </button>
        </nav>
    </div>

    <!-- Subfolder / Pill Filters (Dinamis) -->
    @if(count($subFilters) > 0)
    <div class="mb-6 flex flex-wrap gap-2">
        <!-- Tombol 'Semua' -->
        <button wire:click="setFilter('')"
                class="px-4 py-1.5 text-xs font-semibold rounded-full border transition-all duration-200 {{ $activeFilter === '' ? 'bg-slate-800 text-white border-slate-800 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Semua Data
        </button>
        
        <!-- Render otomatis dari Database -->
        @foreach($subFilters as $filter)
            <button wire:click="setFilter('{{ $filter }}')"
                    class="px-4 py-1.5 text-xs font-semibold rounded-full border transition-all duration-200 {{ $activeFilter === $filter ? 'bg-slate-800 text-white border-slate-800 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                {{ $filter }}
            </button>
        @endforeach
    </div>
    @endif

    <!-- Tabel Data -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $activeTab === 'siswa' ? 'NIS' : ($activeTab === 'guru' ? 'NIP' : 'No. Identitas') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $activeTab === 'siswa' ? 'Kelas & Jurusan' : ($activeTab === 'guru' ? 'Mata Pelajaran' : 'Unit Kerja') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No. HP</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $emp->employee_code }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ $emp->user->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $emp->position ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $emp->phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($emp->status === 'active')
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 rounded-full text-xs font-semibold">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 bg-rose-50 text-rose-700 ring-1 ring-rose-600/20 rounded-full text-xs font-semibold">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-right space-x-3">
                                <button wire:click="edit({{ $emp->id }})" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">Edit</button>
                                <button wire:click="delete({{ $emp->id }})" wire:confirm="Yakin ingin menghapus data ini?" class="text-red-600 hover:text-red-800 font-medium transition-colors">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">
                                @if($activeFilter !== '')
                                    Tidak ada data {{ ucfirst($activeTab) }} di subfolder/kategori "{{ $activeFilter }}".
                                @else
                                    Belum ada data {{ $activeTab }} yang didaftarkan.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Paginasi -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $employees->links() }}
        </div>
    </div>

    <!-- Modal Form Tambah/Edit (Tetap sama seperti sebelumnya) -->
    @if($isFormOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ $isEditing ? 'Edit Data' : 'Tambah Data' }} {{ ucfirst($activeTab) }}
                    </h3>
                    <button wire:click="$set('isFormOpen', false)" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="p-6 space-y-4">
                    <input type="hidden" wire:model="type">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ $activeTab === 'siswa' ? 'Nomor Induk Siswa (NIS)' : ($activeTab === 'guru' ? 'NIP' : 'No. Identitas') }}
                        </label>
                        <input type="text" wire:model="employee_code" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800">
                        @error('employee_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ $activeTab === 'siswa' ? 'Kelas & Jurusan (Misal: 10 IPA 1)' : ($activeTab === 'guru' ? 'Mata Pelajaran / Jabatan' : 'Unit Kerja') }}
                        </label>
                        <input type="text" wire:model="position" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nomor HP</label>
                            <input type="text" wire:model="phone" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status Keanggotaan</label>
                            <select wire:model="status" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800">
                                <option value="active">Aktif</option>
                                <option value="inactive">Non-Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 mt-6 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" wire:click="$set('isFormOpen', false)" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm flex items-center gap-2">
                            <span wire:loading.remove wire:target="save">Simpan Data</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
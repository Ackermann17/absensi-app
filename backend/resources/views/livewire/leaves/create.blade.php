<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Leave;
use App\Models\Employee;

new #[Layout('layouts.app')] class extends Component {
    // Tambahkan variabel untuk menampung inputan NIS
    public $employee_code = ''; 
    public $type = 'sick';
    public $start_date = '';
    public $end_date = '';
    public $reason = '';

    public function rules()
    {
        return [
            // Validasi: Pastikan NIS wajib diisi dan benar-benar ada di tabel employees
            'employee_code' => 'required|exists:employees,employee_code', 
            'type' => 'required|in:sick,annual_leave,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ];
    }

    public function submit()
    {
        $this->validate();

        // Cari data murid/karyawan berdasarkan NIS yang diketik Admin
        $employee = Employee::where('employee_code', $this->employee_code)->first();

        Leave::create([
            'employee_id' => $employee->id,
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reason' => $this->reason,
            // Biarkan 'pending' agar Admin tetap harus meng-approve-nya di menu Approval
            'status' => 'pending', 
        ]);

        $this->reset(['employee_code', 'type', 'start_date', 'end_date', 'reason']);
        
        session()->flash('message', 'Pengajuan izin untuk NIS ' . $employee->employee_code . ' berhasil dikirim.');
    }
}; ?>


<div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md mt-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Form Pengajuan Izin/Cuti</h2>

    @if (session()->has('message'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="submit" class="space-y-4">
        <!-- Input NIS / Kode Karyawan -->
        <div class="mb-4">
            <label for="employee_code" class="block text-sm font-medium text-gray-700">NIS / Kode Siswa</label>
            <input type="text" id="employee_code" wire:model="employee_code" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                placeholder="Masukkan NIS Siswa (Misal: 10101)">
            
            <!-- Menampilkan pesan error jika NIS tidak ditemukan di database -->
            @error('employee_code') 
                <span class="text-red-500 text-xs mt-1 block">{{ $message == 'The employee code field is required.' ? 'NIS wajib diisi.' : 'NIS tidak terdaftar di sistem.' }}</span> 
            @enderror
        </div>
        <!-- Jenis Izin -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Jenis Izin</label>
            <select wire:model="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="sick">Sakit</option>
                <option value="annual_leave">Cuti Tahunan</option>
                <option value="other">Keperluan Lain</option>
            </select>
            @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Tanggal Mulai & Selesai -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                <input type="date" wire:model="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                <input type="date" wire:model="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('end_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Alasan -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Alasan / Keterangan</label>
            <textarea wire:model="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Jelaskan alasan izin..."></textarea>
            @error('reason') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Tombol Submit -->
        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Kirim Pengajuan
            </button>
        </div>
    </form>
</div>
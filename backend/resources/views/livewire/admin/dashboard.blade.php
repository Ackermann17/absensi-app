<?php

use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; // Tambahkan ini
use App\Exports\AttendanceExport;    // Tambahkan ini
use function Livewire\Volt\{computed, layout, action, state};

layout('layouts.app');
state([
    'filterMonth' => '',
    'filterDate' => '',
    'filterStatus' => ''
]);
// 1. Mengambil total seluruh karyawan
$totalEmployees = computed(fn () => Employee::count());

// 2. Mengambil jumlah yang HADIR (on_time / late) hari ini
$presentToday = computed(fn () => 
    Attendance::where('date', Carbon::today()->toDateString())
        ->whereIn('status', ['on_time', 'late'])
        ->count()
);

// 3. Mengambil jumlah yang IZIN / SAKIT hari ini
$izinSakitToday = computed(fn () => 
    Attendance::where('date', Carbon::today()->toDateString())
        ->whereIn('status', ['izin', 'sakit'])
        ->count()
);

// 4. Mengambil jumlah TERLAMBAT hari ini
$lateToday = computed(fn () => 
    Attendance::where('date', Carbon::today()->toDateString())
        ->where('status', 'late')
        ->count()
);
$exportExcel = action(function () {
    $fileName = 'Laporan_Absensi_' . date('Ymd_His') . '.xlsx';
    
    // Kirim data state ke constructor AttendanceExport
    return Excel::download(
        new AttendanceExport($this->filterMonth, $this->filterDate, $this->filterStatus), 
        $fileName
    );
});
// 5. LOGIKA BARU "BELUM ABSEN": Total Karyawan - (Hadir + Izin/Sakit)
$absentToday = computed(function () {
    return $this->totalEmployees - ($this->presentToday + $this->izinSakitToday);
});

// 6. Mengambil 10 data absen terakhir hari ini
$recentAttendances = computed(fn () => 
    Attendance::with(['employee.user'])
        ->where('date', Carbon::today()->toDateString())
        ->latest('updated_at') // Urutkan dari yang terbaru diupdate (termasuk yang baru di-approve)
        ->take(10)
        ->get()
);


?>
<div wire:poll.10s class="p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard Kehadiran Hari Ini</h2>

    <!-- Summary Cards (Diubah jadi grid-cols-5 agar muat 5 kotak) -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <!-- Card Total -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h3 class="text-gray-500 text-sm font-medium">Total Karyawan/Siswa</h3>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $this->totalEmployees }}</p>
        </div>

        <!-- Card Hadir -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-green-500">
            <h3 class="text-gray-500 text-sm font-medium">Hadir Hari Ini</h3>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $this->presentToday }}</p>
        </div>

        <!-- CARD BARU: Izin / Sakit -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
            <h3 class="text-gray-500 text-sm font-medium">Izin / Sakit</h3>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $this->izinSakitToday }}</p>
        </div>

        <!-- Card Belum Absen -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-red-500">
            <h3 class="text-gray-500 text-sm font-medium">Belum Absen</h3>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $this->absentToday }}</p>
        </div>
        
        <!-- Card Terlambat -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-yellow-500">
            <h3 class="text-gray-500 text-sm font-medium">Terlambat</h3>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $this->lateToday }}</p>
        </div>
    </div>

    <!-- Table Recent Attendances -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Aktivitas Absensi Terbaru</h3>
            <span class="text-xs text-gray-500 flex items-center">
                <span class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></span>
                Live Updates
            </span>
        </div>
        
        <table class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-sm font-medium text-gray-500 border-b">Waktu</th>
                    <th class="px-6 py-3 text-sm font-medium text-gray-500 border-b">Nama</th>
                    <th class="px-6 py-3 text-sm font-medium text-gray-500 border-b">Kelas/Posisi</th>
                    <th class="px-6 py-3 text-sm font-medium text-gray-500 border-b">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->recentAttendances as $attendance)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-600 border-b">
                            {{ $attendance->created_at->format('H:i:s') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-800 border-b">
                            {{ $attendance->employee->user->name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 border-b">
                            {{ $attendance->employee->position ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm border-b">
                            <!-- PERBAIKAN: Status Dinamis -->
                            @if($attendance->status === 'on_time')
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Tepat Waktu</span>
                            @elseif($attendance->status === 'late')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Terlambat</span>
                            @elseif($attendance->status === 'sakit')
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">Sakit</span>
                            @elseif($attendance->status === 'izin')
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">Izin</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">{{ ucfirst($attendance->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            Belum ada data absensi hari ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- Tombol Export -->
    <!-- Form Filter & Export -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6 border border-gray-100">
        <h3 class="text-sm font-semibold text-gray-600 mb-3">Filter Export Laporan</h3>
        
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Filter Bulan -->
            <div class="w-full md:w-1/4">
                <label class="block text-xs text-gray-500 mb-1 flex justify-between items-center">
                    <span>Bulan</span>
                    <span class="text-[9px] text-gray-400 italic">*Abaikan jika pilih tanggal</span>
                </label>
                <input type="month" wire:model="filterMonth" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Pilih Bulan">
            </div>

            <!-- Filter Tanggal -->
            <div class="w-full md:w-1/4">
                <label class="block text-xs text-gray-500 mb-1">Tanggal Spesifik (Opsional)</label>
                <input type="date" wire:model="filterDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- Filter Status -->
            <div class="w-full md:w-1/4">
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select wire:model="filterStatus" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="on_time">Hadir (Tepat Waktu)</option>
                    <option value="late">Terlambat</option>
                    <option value="sakit">Sakit</option>
                    <option value="izin">Izin</option>
                </select>
            </div>

            <!-- Tombol Export -->
            <div class="w-full md:w-1/4 pb-1">
                <button wire:click="exportExcel" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm transition duration-150 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Unduh Excel
                </button>
            </div>
        </div>
    </div>
</div>
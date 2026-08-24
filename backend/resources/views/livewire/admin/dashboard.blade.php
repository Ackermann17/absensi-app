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
<div>
    <!-- Header Standar Breeze (Membuat blok putih terpisah dengan jarak rapi) -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard Utama') }}
        </h2>
    </x-slot>

    <!-- Kontainer Utama dengan batas lebar (max-w-7xl) agar sejajar dengan navigasi -->
    <div wire:poll.10s class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- SECTION 1: Summary Cards Kehadiran -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                <h3 class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Total Siswa</h3>
                <p class="text-3xl font-bold text-slate-800 mt-2">{{ $this->totalEmployees }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-emerald-500">
                <h3 class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Hadir Hari Ini</h3>
                <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $this->presentToday }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-blue-500">
                <h3 class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Izin / Sakit</h3>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $this->izinSakitToday }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-rose-500">
                <h3 class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Belum Absen</h3>
                <p class="text-3xl font-bold text-rose-600 mt-2">{{ $this->absentToday }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-amber-500">
                <h3 class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Terlambat</h3>
                <p class="text-3xl font-bold text-amber-600 mt-2">{{ $this->lateToday }}</p>
            </div>
        </div>

        <!-- SECTION 2: Filter & Export Excel -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 mb-8">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <h3 class="text-sm font-bold text-slate-700">Filter & Unduh Laporan</h3>
            </div>
            
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="w-full md:w-1/4">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Bulan</label>
                    <input type="month" wire:model="filterMonth" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-slate-700">
                </div>
                <div class="w-full md:w-1/4">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Tanggal (Opsional)</label>
                    <input type="date" wire:model="filterDate" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-slate-700">
                </div>
                <div class="w-full md:w-1/4">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Kehadiran</label>
                    <select wire:model="filterStatus" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-slate-700">
                        <option value="">Semua Status</option>
                        <option value="on_time">Hadir (Tepat Waktu)</option>
                        <option value="late">Terlambat</option>
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                    </select>
                </div>
                <div class="w-full md:w-1/4">
                    <button wire:click="exportExcel" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition-colors duration-200 h-[38px]">
                        Unduh Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Tabel Aktivitas Terbaru -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800">Aktivitas Absensi Terbaru</h3>
                <span class="text-xs font-medium text-emerald-600 flex items-center bg-emerald-50 px-2 py-1 rounded-full ring-1 ring-emerald-500/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                    Live Updates
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white">
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">Waktu</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">Nama</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">Kelas/Posisi</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($this->recentAttendances as $attendance)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-600">{{ $attendance->created_at->format('H:i:s') }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ $attendance->employee->user->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $attendance->employee->position ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if($attendance->status === 'on_time')
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 rounded-full text-xs font-semibold">Tepat Waktu</span>
                                    @elseif($attendance->status === 'late')
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 rounded-full text-xs font-semibold">Terlambat</span>
                                    @elseif($attendance->status === 'sakit')
                                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 rounded-full text-xs font-semibold">Sakit</span>
                                    @elseif($attendance->status === 'izin')
                                        <span class="px-2.5 py-1 bg-purple-50 text-purple-700 ring-1 ring-purple-600/20 rounded-full text-xs font-semibold">Izin</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 ring-1 ring-slate-500/20 rounded-full text-xs font-semibold">{{ ucfirst($attendance->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 text-sm">Belum ada data absensi yang terekam hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
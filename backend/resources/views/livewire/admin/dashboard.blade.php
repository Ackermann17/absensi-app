<?php

use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use function Livewire\Volt\{computed, layout};

layout('layouts.app');

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
</div>
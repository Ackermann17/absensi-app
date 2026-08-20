<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Leave;
use App\Models\Attendance;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'leaves' => Leave::with('employee')->latest()->paginate(10),
        ];
    }

    public function approve(Leave $leave)
    {
        // 1. Ubah status izin menjadi approved
        $leave->update(['status' => 'approved']);
        
        // 2. Siapkan rentang tanggal
        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);
        
        // 3. Tentukan status absensi (sakit atau izin)
        $attendanceStatus = $leave->type === 'sick' ? 'sakit' : 'izin';
        
        // 4. Looping dari tanggal mulai sampai tanggal selesai
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            
            // 5. Masukkan ke tabel attendances
            // 4. Looping dari tanggal mulai sampai tanggal selesai
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            
            // Masukkan ke tabel attendances, dan TANGKAP DATANYA ke variabel $attendance
            $attendance = \App\Models\Attendance::updateOrCreate(
                [
                    'employee_id' => $leave->employee_id,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'status' => $attendanceStatus,
                    'check_in' => null,
                    'check_out' => null,
                    'late_duration' => 0,
                ]
            );

            // --- TAMBAHAN BARU: PICU NOTIFIKASI WA ---
            \App\Jobs\SendWhatsAppNotification::dispatch($attendance, $attendanceStatus);
            // -----------------------------------------
            }
        }
        
        session()->flash('message', 'Pengajuan izin berhasil disetujui dan data absensi otomatis diperbarui.');
    }

    // INI ADALAH FUNGSI YANG HILANG TADI:
    public function reject(Leave $leave)
    {
        $leave->update(['status' => 'rejected']);
        session()->flash('message', 'Pengajuan izin ditolak.');
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Pengajuan Izin & Cuti</h2>

                @if (session()->has('message'))
                    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($leaves as $leave)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $leave->employee->name ?? 'User Dihapus' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($leave->type == 'sick') Sakit
                                        @elseif($leave->type == 'annual_leave') Cuti
                                        @else Keperluan Lain
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} - 
                                        {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        {{ $leave->reason }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($leave->status === 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($leave->status === 'approved')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($leave->status === 'pending')
                                            <button wire:click="approve({{ $leave->id }})" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                            <button wire:click="reject({{ $leave->id }})" class="text-red-600 hover:text-red-900">Reject</button>
                                        @else
                                            <span class="text-gray-400">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Belum ada data pengajuan izin.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $leaves->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Employee;
use App\Models\Attendance;
use App\Jobs\SendWhatsAppNotification;
use Carbon\Carbon;

new #[Layout('layouts.guest')] class extends Component {
    public $employee_code = '';
    public $message = '';
    public $messageType = 'success';

    public function processAttendance()
    {
        $this->validate([
            'employee_code' => 'required'
        ]);

        $employee = Employee::where('employee_code', $this->employee_code)->first();

        if (!$employee) {
            $this->message = 'Kode tidak ditemukan! Silakan coba lagi.';
            $this->messageType = 'error';
            $this->employee_code = '';
            return;
        }

        // Simpan instance $now agar waktu yang digunakan persis sama
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if (!$attendance->exists) {
            // --- LOGIKA CHECK-IN & KETERLAMBATAN ---
            $attendance->check_in = $currentTime;
            
            // Tentukan batas jam masuk, misalnya jam 08:00:00
            $batasMasuk = Carbon::today()->setTime(8, 0, 0);

            if ($now->greaterThan($batasMasuk)) {
                $attendance->status = 'late';
                // Hitung selisih menit keterlambatan (dibulatkan menjadi bilangan bulat)
                $attendance->late_duration = (int) $batasMasuk->diffInMinutes($now);
            } else {
                $attendance->status = 'on_time';
                $attendance->late_duration = null;
            }
            
            $attendance->save();
            
            // Sesuaikan pesan UI berdasarkan status
            $this->message = "Berhasil Check-In: {$employee->employee_code} pada {$currentTime}";
            if ($attendance->status === 'late') {
                $this->message .= " (Terlambat {$attendance->late_duration} menit)";
                // Opsional: Ubah warna alert jadi error/kuning jika telat
                $this->messageType = 'error'; 
            } else {
                $this->messageType = 'success';
            }
            
            SendWhatsAppNotification::dispatch($attendance, 'check_in');
            
        } elseif (is_null($attendance->check_out)) {
            // --- LOGIKA CHECK-OUT ---
            $attendance->check_out = $currentTime;
            $attendance->save();
            
            $this->message = "Berhasil Check-Out: {$employee->employee_code} pada {$currentTime}";
            $this->messageType = 'success';
            
            SendWhatsAppNotification::dispatch($attendance, 'check_out');
            
        } else {
            // Sudah absen masuk & keluar
            $this->message = "Anda sudah menyelesaikan absensi untuk hari ini.";
            $this->messageType = 'error';
        }

        $this->employee_code = '';
    }
}; ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl text-center border border-gray-100">
        <h2 class="text-3xl font-extrabold mb-2 text-gray-800">Terminal Absensi</h2>
        <p class="text-gray-500 mb-6 text-sm">Silakan scan kode QR atau ketik manual</p>
        
        <!-- Notifikasi Pesan -->
        @if($message)
            <div class="p-4 mb-6 rounded-lg font-medium text-sm {{ $messageType === 'success' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' : 'bg-red-100 text-red-800 border-l-4 border-red-500' }}">
                {{ $message }}
            </div>
        @endif

        <form wire:submit="processAttendance">
            <div class="mb-6">
                <input type="text" wire:model="employee_code" id="employee_code" 
                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-center text-2xl font-bold uppercase tracking-widest"
                    placeholder="KODE" autocomplete="off" autofocus>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 px-4 rounded-xl hover:bg-blue-700 active:transform active:scale-95 transition-all shadow-lg hover:shadow-blue-500/30">
                PROSES ABSEN
            </button>
        </form>
        
        <!-- Jam Digital (Livewire Poll) -->
        <div class="mt-8 pt-6 border-t border-gray-100">
            <p class="text-sm text-gray-400 font-medium tracking-wider">WAKTU LOKAL</p>
            <div class="text-xl font-bold text-gray-700 mt-1" wire:poll.1s>
                {{ \Carbon\Carbon::now()->format('H:i:s') }} WIT
            </div>
        </div>
    </div>
</div>
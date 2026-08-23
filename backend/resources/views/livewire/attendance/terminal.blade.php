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

        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);
        
        if ($attendance->exists && in_array($attendance->status, ['sakit', 'izin'])) {
            $this->message = "Anda tercatat sedang " . ucfirst($attendance->status) . " hari ini.";
            $this->messageType = 'error';
            $this->employee_code = '';
            return;
        }

        if (!$attendance->exists) {
            $attendance->check_in = $currentTime;
            $batasMasuk = Carbon::today()->setTime(8, 0, 0);

            if ($now->greaterThan($batasMasuk)) {
                $attendance->status = 'late';
                $attendance->late_duration = (int) $batasMasuk->diffInMinutes($now);
            } else {
                $attendance->status = 'on_time';
                $attendance->late_duration = null;
            }
            
            $attendance->save();
            
            $this->message = "Berhasil Check-In: {$employee->employee_code} pada {$currentTime}";
            if ($attendance->status === 'late') {
                $this->message .= " (Terlambat {$attendance->late_duration} menit)";
                $this->messageType = 'error'; 
            } else {
                $this->messageType = 'success';
            }
            
            SendWhatsAppNotification::dispatch($attendance, 'check_in');
            
        } elseif (is_null($attendance->check_out)) {
            // --- LOGIKA CHECK-OUT DENGAN COOLDOWN ---
            
            // 1. Gabungkan tanggal dan waktu check-in agar bisa dihitung
            $waktuCheckIn = Carbon::parse($attendance->date . ' ' . $attendance->check_in);
            
            // 2. Hitung selisih menit dan langsung bulatkan menjadi angka utuh (integer)
            $selisihMenit = (int) $waktuCheckIn->diffInMinutes($now);

            // 3. Tentukan batas minimal waktu (Misal: 30 menit)
            $minimalMenit = 30;

            // 4. Jika selisih waktu masih di bawah batas minimal, tolak check-out!
            if ($selisihMenit < $minimalMenit) {
                // Sisa waktu dipastikan akan bernilai bulat
                $sisaWaktu = $minimalMenit - $selisihMenit;
                
                $this->message = "Terlalu cepat! Anda baru saja absen masuk. Tunggu {$sisaWaktu} menit lagi untuk Check-Out.";
                $this->messageType = 'error';
                $this->employee_code = '';
                return; // Hentikan proses di sini
            }

            // Jika sudah lewat batas waktu, izinkan check-out
            $attendance->check_out = $currentTime;
            $attendance->save();
            
            $this->message = "Berhasil Check-Out: {$employee->employee_code} pada {$currentTime}";
            $this->messageType = 'success';
            
            SendWhatsAppNotification::dispatch($attendance, 'check_out');
            
        } else {
            $this->message = "Anda sudah menyelesaikan absensi untuk hari ini.";
            $this->messageType = 'error';
        }

        $this->employee_code = '';
    }
}; ?>

<!-- SEMUA KODE SEKARANG BERADA DI DALAM SATU DIV ROOT UNTUK LIVEWIRE -->
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-10 relative">
    
    <style>
        /* Gaya Khusus Scanner */
        #reader { width: 100% !important; border: none !important; background: transparent !important; }
        #reader__scan_region { width: 100% !important; min-height: 250px !important; display: flex !important; justify-content: center !important; align-items: center !important; overflow: hidden !important; border-radius: 0.75rem !important; border: 2px solid #e5e7eb !important; background-color: #f8fafc !important; }
        #reader__scan_region video { width: 100% !important; max-width: 100% !important; height: auto !important; min-height: 250px !important; object-fit: cover !important; border-radius: 0.75rem !important; transform: scaleX(-1) !important;}
        #reader__scan_region img { display: none !important; }
        #reader__dashboard { width: 100% !important; padding: 15px 0 !important; }
        #reader select { width: 100% !important; max-width: 300px !important; padding: 10px !important; border-radius: 8px !important; border: 1px solid #d1d5db !important; margin: 10px auto !important; display: block !important; font-size: 14px !important; background-color: white !important; }
        #reader button { background-color: #2563eb !important; color: white !important; padding: 10px 20px !important; border-radius: 8px !important; border: none !important; font-weight: 600 !important; cursor: pointer; margin-top: 10px !important; transition: all 0.2s !important; }
        #reader button:hover { background-color: #1d4ed8 !important; }

        /* ANIMASI CSS MURNI UNTUK NOTIFIKASI */
        .toast-anim {
            animation: slideDownAndUp 4s ease-in-out forwards;
        }
        @keyframes slideDownAndUp {
            0% { opacity: 0; transform: translate(-50%, -20px); visibility: visible; }
            10% { opacity: 1; transform: translate(-50%, 0); visibility: visible; }
            85% { opacity: 1; transform: translate(-50%, 0); visibility: visible; }
            100% { opacity: 0; transform: translate(-50%, -20px); visibility: hidden; pointer-events: none; }
        }
    </style>

    <!-- NOTIFIKASI MELAYANG CSS MURNI -->
    @if($message)
        <div wire:key="toast-{{ rand() }}" class="toast-anim fixed top-8 left-1/2 z-[9999] w-[90%] max-w-lg p-5 rounded-2xl shadow-xl font-bold text-center border-2 text-lg backdrop-blur-md 
            {{ $messageType === 'success' ? 'bg-green-100/95 text-green-800 border-green-500' : 'bg-red-100/95 text-red-800 border-red-500' }}">
            {{ $messageType === 'success' ? '✅' : '⚠️' }} {{ $message }}
        </div>
    @endif

    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl text-center border border-gray-100">
        
        <h2 class="text-3xl font-extrabold mb-2 text-gray-800">Terminal Absensi</h2>
        <p class="text-gray-500 mb-6 text-sm">Silakan scan kode barcode/QR atau ketik manual</p>

        <!-- Area Kamera Scanner -->
        <div wire:ignore class="w-full mx-auto mb-6 relative">
            <div id="reader"></div>
        </div>

        <!-- Form Manual -->
        <form wire:submit="processAttendance">
            <div class="mb-6">
                <input type="text" wire:model="employee_code" id="employee_code" 
                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-center text-2xl font-bold uppercase tracking-widest"
                    placeholder="KODE" autocomplete="off">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 px-4 rounded-xl hover:bg-blue-700 active:transform active:scale-95 transition-all shadow-lg hover:shadow-blue-500/30">
                PROSES ABSEN MANUAL
            </button>
        </form>
        <!-- Jam Digital (JavaScript Murni, tanpa beban server) -->
        <div class="mt-8 pt-6 border-t border-gray-100">
            <p class="text-sm text-gray-400 font-medium tracking-wider">WAKTU LOKAL</p>
            <div id="realtime-clock" class="text-xl font-bold text-gray-700 mt-1">
                {{ \Carbon\Carbon::now()->format('H:i:s') }} WIT
            </div>
        </div>
    </div>

    <!-- Tambahkan Library Scanner JS -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <!-- Integrasi Livewire Volt dan Scanner -->
    @script
    <script>
        let isProcessing = false;
        
        const html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 }, 
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
            },
            false
        );

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;

            html5QrcodeScanner.pause(true);

            $wire.employee_code = decodedText;

            $wire.processAttendance().then(() => {
                setTimeout(() => {
                    html5QrcodeScanner.resume();
                    isProcessing = false;
                }, 4000); // Sinkronkan dengan animasi CSS (4 detik)
            }).catch(() => {
                setTimeout(() => {
                    html5QrcodeScanner.resume();
                    isProcessing = false;
                }, 4000);
            });
        }

        function onScanFailure(error) {
            // Abaikan error pembacaan frame
        }

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
    @endscript
    <script>
        setInterval(() => {
            const clock = document.getElementById('realtime-clock');
            if (clock) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clock.innerText = `${hours}:${minutes}:${seconds} WIT`;
            }
        }, 1000);
    </script>

</div>
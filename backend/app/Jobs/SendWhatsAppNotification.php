<?php

namespace App\Jobs;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $attendance;
    public $type; // 'check_in' atau 'check_out'

    /**
     * Create a new job instance.
     */
    public function __construct(Attendance $attendance, $type)
    {
        $this->attendance = $attendance;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Ambil relasi murid/karyawan
        $student = $this->attendance->employee; 
        
        // Gunakan kolom phone sesuai tabel Anda
        $targetNumber = $student->phone; 

        if (!$targetNumber) {
            Log::warning("Nomor WA tidak ditemukan untuk ID: {$student->id}");
            return;
        }

        // 2. Susun Pesan
        $time = $this->type === 'check_in' ? $this->attendance->check_in : $this->attendance->check_out;
        $statusText = $this->type === 'check_in' ? 'Tiba di sekolah' : 'Pulang dari sekolah';
        
        // Mengambil nama dari tabel users
        $studentName = $student->user->name ?? 'Murid'; 
        
        // Mengambil kelas dari kolom position di tabel employees
        $studentClass = $student->position ?? 'Kelas tidak diketahui'; 
        
        // Gabungkan Nama dan Kelas di dalam format tebal (bold) WhatsApp
        $message = "Halo Bapak/Ibu, ananda *{$studentName} ({$studentClass})* telah {$statusText} pada pukul {$time} WIT.";

        // --- TAMBAHAN LOGIKA KETERLAMBATAN ---
        // Cek apakah ini absen masuk DAN statusnya terlambat
        if ($this->type === 'check_in' && $this->attendance->status === 'late') {
            $message .= "\n\n⚠️ *Catatan:* Ananda tercatat datang *terlambat {$this->attendance->late_duration} menit* dari jam masuk standar.";
        }
        // -------------------------------------

        // 3. Kirim via API (Menggunakan ->withoutVerifying() untuk bypass SSL Localhost)
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $targetNumber,
                'message' => $message,
                'countryCode' => '62', // Default Indonesia
            ]);

            if (!$response->successful()) {
                Log::error('Gagal mengirim WA API: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Exception WA API: ' . $e->getMessage());
            throw $e; 
        }
    }
}
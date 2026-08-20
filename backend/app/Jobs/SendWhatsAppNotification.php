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
        $targetNumber = $student->phone; 

        if (!$targetNumber) {
            Log::warning("Nomor WA tidak ditemukan untuk ID: {$student->id}");
            return;
        }

        // Mengambil nama dan kelas
        $studentName = $student->user->name ?? 'Murid'; 
        $studentClass = $student->position ?? 'Kelas tidak diketahui'; 
        
        // 2. Susun Pesan Berdasarkan Tipe
        if ($this->type === 'check_in') {
            $message = "Halo Bapak/Ibu, ananda *{$studentName} ({$studentClass})* telah *Tiba di sekolah* pada pukul {$this->attendance->check_in} WIT.";
            
            if ($this->attendance->status === 'late') {
                $message .= "\n\n⚠️ *Catatan:* Ananda tercatat datang *terlambat {$this->attendance->late_duration} menit* dari jam masuk standar.";
            }
        } elseif ($this->type === 'check_out') {
            $message = "Halo Bapak/Ibu, ananda *{$studentName} ({$studentClass})* telah *Pulang dari sekolah* pada pukul {$this->attendance->check_out} WIT.";
        } elseif ($this->type === 'sakit') {
            $message = "Halo Bapak/Ibu, ini adalah pemberitahuan dari sekolah bahwa pengajuan izin untuk ananda *{$studentName} ({$studentClass})* telah disetujui dengan status *SAKIT* pada tanggal {$this->attendance->date}. Semoga lekas sembuh.";
        } elseif ($this->type === 'izin') {
            $message = "Halo Bapak/Ibu, pengajuan izin untuk ananda *{$studentName} ({$studentClass})* pada tanggal {$this->attendance->date} telah *DISETUJUI* oleh pihak sekolah.";
        } else {
            return; // Jika tipenya tidak dikenal, hentikan proses
        }

        // 3. Kirim via API Fonnte
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $targetNumber,
                'message' => $message,
                'countryCode' => '62',
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
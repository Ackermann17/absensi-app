<?php

use App\Jobs\SendWhatsAppNotification;
use App\Models\Employee;
use Illuminate\Support\Facades\Queue;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mendispach job whatsapp saat murid berhasil check-in', function () {
    Queue::fake();

    $employee = Employee::factory()->create([
        'employee_code' => '12345',
        // 'parent_phone' => '081234567890', // Aktifkan jika kolom ini sudah ada di database
    ]);

    // Menguji komponen attendance.terminal
    Volt::test('attendance.terminal')
        ->set('employee_code', '12345') // Sesuai dengan public $employee_code
        ->call('processAttendance')     // DISESUAIKAN: memanggil fungsi processAttendance()
        ->assertHasNoErrors();

    // Memastikan Job masuk ke antrean
    Queue::assertPushed(SendWhatsAppNotification::class, function ($job) use ($employee) {
        return $job->attendance->employee_id === $employee->id 
            && $job->type === 'check_in';
    });
});

it('mendispach job whatsapp saat murid berhasil check-out', function () {
    Queue::fake();

    $employee = Employee::factory()->create([
        'employee_code' => '12345',
    ]);

    // Membuat data absensi masuk (Check-In) terlebih dahulu
    \App\Models\Attendance::factory()->create([
        'employee_id' => $employee->id,
        'date' => now()->toDateString(),
        'check_in' => now()->subHours(5),
    ]);

    // Simulasi scan kedua untuk Check-Out
    Volt::test('attendance.terminal')
        ->set('employee_code', '12345')
        ->call('processAttendance')     // DISESUAIKAN: memanggil fungsi processAttendance()
        ->assertHasNoErrors();

    // Memastikan Job masuk ke antrean
    Queue::assertPushed(SendWhatsAppNotification::class, function ($job) use ($employee) {
        return $job->attendance->employee_id === $employee->id 
            && $job->type === 'check_out';
    });
});
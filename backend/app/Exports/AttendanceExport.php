<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $month;
    protected $date;
    protected $status;

    // Menerima parameter filter saat class dipanggil
    public function __construct($month = null, $date = null, $status = null)
    {
        $this->month = $month;
        $this->date = $date;
        $this->status = $status;
    }

    public function collection(): Collection
{
    $query = Attendance::with('employee')->latest('date');

    if (!empty($this->status)) {
        $query->where('status', $this->status);
    }

    if (!empty($this->date)) {
        $query->whereDate('date', $this->date);
    } elseif (!empty($this->month)) {
        $parsedDate = Carbon::parse($this->month);
        $query->whereMonth('date', $parsedDate->month)
              ->whereYear('date', $parsedDate->year);
    }

    return $query->get();
}

    public function headings(): array
    {
        return [
            'ID', 'NIS', 'Nama Lengkap', 'Tanggal', 'Waktu Masuk', 'Waktu Keluar', 'Status', 'Dibuat Pada'
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->id,
            $attendance->employee->employee_code ?? '-',
            $attendance->employee->user->name ?? '-',
            $attendance->date,
            $attendance->check_in ?? '-',
            $attendance->check_out ?? '-',
            strtoupper($attendance->status),
            $attendance->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
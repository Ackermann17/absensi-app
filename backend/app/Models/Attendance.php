<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = []; // Mengizinkan semua kolom untuk diisi

    // Tambahkan relasi ini agar Job bisa memanggil $this->attendance->employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
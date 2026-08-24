<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Menambahkan kolom 'type' untuk pengelompokan utama
            $table->enum('type', ['siswa', 'guru', 'pegawai'])->default('siswa')->after('name');
            
            // Opsional: Anda bisa mengganti nama kolom emp_code menjadi identity_number
            // agar lebih relevan untuk NIS/NIP. Jika menggunakan PostgreSQL, jalankan ini:
            // $table->renameColumn('emp_code', 'identity_number');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('type');
            // $table->renameColumn('identity_number', 'emp_code');
        });
    }
};
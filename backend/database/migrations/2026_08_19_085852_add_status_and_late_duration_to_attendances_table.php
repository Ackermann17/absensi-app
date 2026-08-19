<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Karena kolom 'status' sudah ada, kita lewati saja
            // $table->string('status')->default('on_time')->after('employee_id');
            
            // Kita hanya perlu menambahkan kolom 'late_duration'
            $table->integer('late_duration')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['status', 'late_duration']);
        });
    }
};

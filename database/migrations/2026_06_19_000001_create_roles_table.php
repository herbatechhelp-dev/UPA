<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'Superadmin', 'Admin', 'Ustad (Pembina)', 'Ketua Kelompok', 'User (Anggota)'
            $table->string('slug')->unique(); // e.g., 'superadmin', 'admin', 'ustad', 'leader', 'member'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('ustad_id')->nullable()->constrained('users')->onDelete('set null'); // Ustad / Pembina
            $table->foreignId('leader_id')->nullable()->constrained('users')->onDelete('set null'); // Ketua Kelompok
            $table->boolean('is_delegated')->default(false); // Penanda transisi hak approval ke Ketua Kelompok
            $table->timestamp('delegated_until')->nullable(); // Batas kedaluwarsa delegasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};

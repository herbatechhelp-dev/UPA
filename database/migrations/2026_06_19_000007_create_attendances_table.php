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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Member checking in
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade'); // Kajian session
            $table->enum('status', ['present', 'absent', 'sick', 'permission'])->default('absent');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Approved by Ustad or delegated Ketua Kelompok
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'activity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

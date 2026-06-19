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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Member graded
            $table->foreignId('ustad_id')->constrained('users')->onDelete('cascade'); // Ustad giving grade
            $table->tinyInteger('month')->unsigned(); // 1 - 12
            $table->integer('year');
            $table->decimal('score', 5, 2); // Score (e.g. 95.50)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']); // One grade report per user per month
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};

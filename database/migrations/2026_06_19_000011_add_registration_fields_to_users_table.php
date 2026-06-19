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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('gender', ['ikhwan', 'akhwat'])->nullable()->after('phone');
            $table->string('department')->nullable()->after('gender');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('department');
        });

        // Make role_id nullable — role is assigned on approval, not on registration
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'gender', 'department', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
        });
    }
};

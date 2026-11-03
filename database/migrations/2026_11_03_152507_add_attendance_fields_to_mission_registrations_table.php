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
        Schema::table('mission_registrations', function (Blueprint $table) {
            $table->json('attendance_dates')->nullable()->after('email');
            $table->string('attendance_mode', 20)->nullable()->after('attendance_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_registrations', function (Blueprint $table) {
            $table->dropColumn(['attendance_dates', 'attendance_mode']);
        });
    }
};

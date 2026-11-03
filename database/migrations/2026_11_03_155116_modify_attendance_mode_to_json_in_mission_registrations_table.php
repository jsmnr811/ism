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
            $table->dropColumn('attendance_mode');
        });
        Schema::table('mission_registrations', function (Blueprint $table) {
            $table->json('attendance_mode')->nullable()->after('attendance_dates');
        });
    }

    public function down(): void
    {
        Schema::table('mission_registrations', function (Blueprint $table) {
            $table->dropColumn('attendance_mode');
        });
        Schema::table('mission_registrations', function (Blueprint $table) {
            $table->string('attendance_mode', 20)->nullable()->after('attendance_dates');
        });
    }
};

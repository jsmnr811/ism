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
            $table->string('source', 20)->default('landing')->after('attendance_mode');
            $table->foreignId('registered_by')->nullable()->after('source')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registered_by');
            $table->dropColumn('source');
        });
    }
};

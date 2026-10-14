<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('report_name')->nullable()->after('reports');
        });

        Schema::table('site_visits', function (Blueprint $table) {
            $table->date('date')->nullable()->after('virtual_site_visit_link');
        });

        Schema::table('mission_registrations', function (Blueprint $table) {
            $table->string('email')->nullable()->after('sex');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('report_name');
        });

        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn('date');
        });

        Schema::table('mission_registrations', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};

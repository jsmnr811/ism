<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->string('last_name');
            $table->string('office');
            $table->string('management_tier')->nullable();
            $table->string('pso_cluster')->nullable();
            $table->string('rpco_office')->nullable();
            $table->string('position');
            $table->string('component_unit')->nullable();
            $table->string('contact_number');
            $table->string('sex');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_registrations');
    }
};

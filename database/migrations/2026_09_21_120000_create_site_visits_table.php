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
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('sp_name');
            $table->string('cluster')->nullable();
            $table->string('component')->nullable();
            $table->string('location')->nullable();
            $table->string('funding_source')->nullable();
            $table->string('document_link')->nullable();
            $table->string('virtual_site_visit_link')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};

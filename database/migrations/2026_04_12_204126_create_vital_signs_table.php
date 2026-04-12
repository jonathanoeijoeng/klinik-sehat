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
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            // Foreign key ke tabel outpatient_visits
            $table->foreignId('visit_id')->constrained('outpatient_visits')->onDelete('cascade');
            
            $table->integer('systole')->nullable();
            $table->integer('diastole')->nullable();
            $table->decimal('weight', 5, 2)->nullable(); // contoh: 120.50
            $table->integer('height')->nullable();
            $table->decimal('temperature', 4, 1)->nullable(); // contoh: 36.5
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};

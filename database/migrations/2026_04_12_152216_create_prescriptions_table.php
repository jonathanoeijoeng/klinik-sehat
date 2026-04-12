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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outpatient_visit_id')->constrained()->cascadeOnDelete();

            // Status Farmasi
            $table->enum('status', ['pending', 'preparing', 'ready', 'dispensed'])->default('pending');

            // Integrasi SATUSEHAT
            $table->string('satusehat_medication_request_id')->nullable();
            $table->string('satusehat_medication_dispense_id')->nullable();

            // Timestamp untuk TAT Farmasi
            $table->timestamp('received_at')->nullable();    // Resep masuk ke apotek
            $table->timestamp('started_at')->nullable();     // Mulai diracik
            $table->timestamp('handed_over_at')->nullable(); // Diserahkan ke pasien

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};

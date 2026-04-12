<?php

namespace App\Jobs;

use App\Models\OutpatientVisit;
use App\Services\SatuSehatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncEncounterToSatuSehat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $visit;

    public $tries = 3;

    /**
     * Waktu tunggu (dalam detik) sebelum mencoba ulang Job yang gagal.
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(OutpatientVisit $visit)
    {
        $this->visit = $visit;
    }

    /**
     * Execute the job.
     */
    public function handle(SatuSehatService $service)
    {
        // Kirim Encounter (Arrived)
        // Cukup kirim objek modelnya saja
        $response = $service->createEncounter($this->visit);

        if ($response->successful()) {
            $encounterId = $response->json('id');
            
            // Simpan ID yang didapat ke database
            $this->visit->update([
                'satusehat_encounter_id' => $encounterId,
            ]);
            
            Log::info("Encounter ID berhasil disimpan: " . $encounterId);
        } else {
            Log::error("SATUSEHAT Error: " . $response->body());
        }
            
            // // Opsional: Kirim Observation (Vital Signs) jika datanya ada
            // if ($this->visit->systole && $this->visit->diastole) {
            //     $service->createObservationBloodPressure($this->visit);
            // }
    }
}

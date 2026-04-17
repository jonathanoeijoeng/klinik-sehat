<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\SatuSehatService;
use App\Models\OutpatientVisit;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FinalizeVisitJob implements ShouldQueue
{
    use Queueable;

    public $visit;
    public $tries = 3;
    public $backoff = 60;


    /**
     * Create a new job instance.
     */
    public function __construct($visit)
    {
        $this->visit = $visit;
    }

    /**
     * Execute the job.
     */
    public function handle(SatuSehatService $service)
    {
        // 1. Update ke SatuSehat
        $resEncounter = $service->updateEncounterStatusAndDiagnosis($this->visit, 'finished');

        if (isset($resEncounter['id'])) {
            DB::transaction(function () {
                // Logika kalkulasi invoice kamu pindah ke sini
                $this->visit->load('prescriptions.medicine');

                $totalMedicineFee = $this->visit->prescriptions->reduce(function ($carry, $p) {
                    return $carry + (($p->medicine->het_price ?? 0) * ($p->qty_ordered ?? 0));
                }, 0);

                $this->visit->update([
                    'status' => 'finished',
                    'finished_at' => now(),
                ]);
            });

            Log::info("Kunjungan {$this->visit->id} berhasil difinalisasi.");
        }
    }
}

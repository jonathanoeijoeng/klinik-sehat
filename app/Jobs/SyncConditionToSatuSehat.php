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

class SyncConditionToSatuSehat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $visit;
    public $tries = 3;
    public $backoff = 60;

    public function __construct(OutpatientVisit $visit)
    {
        $this->visit = $visit;
    }

    public function handle(SatuSehatService $service)
{
    // 1. Pastikan Encounter ID sudah ada (hasil dari Job sebelumnya)
    if (!$this->visit->satusehat_encounter_id) {
        return $this->release(30); 
    }

    // 2. Cari data diagnosa melalui relasi
    // Asumsi: OutpatientVisit hasMany Diagnosis (karena di script kamu ada foreach $this->visit->diagnoses)
    $diagnoses = $this->visit->diagnoses()->whereNull('satusehat_condition_id')->get();

    if ($diagnoses->isEmpty()) {
        Log::info("Tidak ada diagnosa baru untuk disinkronkan pada Visit ID: " . $this->visit->id);
        return;
    }

    // 3. Loop diagnosa yang belum tersinkron
    foreach ($diagnoses as $diag) {
        // Panggil service dengan model diagnosa dan model visit
        $res = $service->sendCondition($diag, $this->visit);

        if (isset($res['id'])) {
            // Update ID di database lokal Intel NUC
            $diag->update(['satusehat_condition_id' => $res['id']]);
            Log::info("Diagnosa {$diag->icd10_code} berhasil sinkron.");
        } else {
            Log::error("Gagal sinkron diagnosa {$diag->icd10_code}: " . json_encode($res));
            // Kita tidak throw exception di sini agar looping obat lain tetap jalan
        }
    }
}
}

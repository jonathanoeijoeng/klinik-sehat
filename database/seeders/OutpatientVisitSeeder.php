<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Icd10;
use App\Models\Invoice;
use App\Models\OutPatientDiagnosis;
use App\Models\OutpatientVisit;
use App\Models\Practitioner;
use App\Models\VitalSign;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OutpatientVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $initial = Clinic::find(1)->initial;
        $internalStatuses = [
            'arrived',
            'at_practitioner',
            'sent_to_pharmacy',
            'sent_for_payment',
            'paid',
            'dispensed',
            'finished',
            'cancelled',
        ];

        $minimumPerStatus = 10;
        $minimumFinishedData = (($minimumPerStatus * (count($internalStatuses) - 1)) + 1);
        $minimumSeederData = ($minimumPerStatus * (count($internalStatuses) - 1)) + $minimumFinishedData;
        $jumlahData = max(Cache::store('file')->get('seeder_jumlah_data', 1267), $minimumSeederData);
        $rentangHari = Cache::store('file')->get('seeder_rentang_hari', 53);

        $statusPlan = collect($internalStatuses)
            ->reject(fn ($status) => $status === 'finished')
            ->flatMap(fn ($status) => array_fill(0, $minimumPerStatus, $status))
            ->concat(array_fill(0, $jumlahData - ($minimumPerStatus * (count($internalStatuses) - 1)), 'finished'))
            ->shuffle()
            ->values();

        $arrivedVisitCount = 0;
        $arrivedVisitsWithoutEncounter = 3;

        for ($i = 0; $i < $jumlahData; $i++) {
            $internalStatus = $statusPlan[$i];
            $shouldCreateEncounter = true;

            if ($internalStatus === 'arrived') {
                $arrivedVisitCount++;
                $shouldCreateEncounter = $arrivedVisitCount > $arrivedVisitsWithoutEncounter;
            }

            // 1. Tentukan Waktu Kedatangan (Arrived)
            $baseTime = Carbon::now()->subDays(rand(1, $rentangHari))->setTime(rand(8, 18), rand(0, 59));

            $visitNumber = $initial . '-' . $baseTime->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $invoiceNumber = 'INV-' . $baseTime->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            // Simulasi Alur Waktu (TAT)
            $arrivedAt = $baseTime;
            $inProgressAt = $arrivedAt->copy()->addMinutes(rand(10, 30)); // Tunggu 10-30 menit
            $sentToPharmacyAt = $inProgressAt->copy()->addMinutes(rand(15, 30));
            $sentForPaymentAt = $sentToPharmacyAt->copy()->addMinutes(rand(5, 15));
            $paidAt = $sentForPaymentAt->copy()->addMinutes(rand(5, 15));
            $dispensedAt = $paidAt->copy()->addMinutes(rand(5, 15));
            $finishedAt = $dispensedAt->copy()->addMinutes(rand(1, 5));
            $cancelledAt = $arrivedAt->copy()->addMinutes(rand(5, 45));

            $icd10Stock = Icd10::inRandomOrder()->limit(50)->get();

            $doctorId = rand(1, 10);
            $practitioner = Practitioner::find($doctorId);

            $regFee = 50000; // Contoh Biaya Pendaftaran Tetap
            $practitionerFee = $practitioner->fee ?? 50000;

            $totalMedicinePrice = 0;

            // 2. Buat Outpatient Visit
            $visit = OutpatientVisit::create([
                'clinic_id'       => 1, // Asumsi id klinik 1
                'visit_number'    => $visitNumber,
                'patient_id'      => rand(1, 10),
                'practitioner_id' => $doctorId,
                'location_id'     => rand(1, 3), // ID Poli
                'status'          => $this->visitStatusForInternalStatus($internalStatus),
                'internal_status' => $internalStatus,
                'satusehat_encounter_id' => $shouldCreateEncounter ? (string) Str::uuid() : null,
                'complaint'       => 'Keluhan umum pasien ke-' . ($i + 1),

                // Timestamp TAT
                'arrived_at'      => $arrivedAt,
                'in_progress_at'  => $this->hasReachedInternalStatus($internalStatus, 'at_practitioner') ? $inProgressAt : null,
                'finished_at'     => $this->hasReachedInternalStatus($internalStatus, 'finished') ? $finishedAt : null,
                'cancelled_at'    => $internalStatus === 'cancelled' ? $cancelledAt : null,
                'at_practitioner_at' => $this->hasReachedInternalStatus($internalStatus, 'at_practitioner') ? $inProgressAt : null,
                'sent_to_pharmacy_at' => $this->hasReachedInternalStatus($internalStatus, 'sent_to_pharmacy') ? $sentToPharmacyAt : null,
                'sent_for_payment_at' => $this->hasReachedInternalStatus($internalStatus, 'sent_for_payment') ? $sentForPaymentAt : null,
                'paid_at'         => $this->hasReachedInternalStatus($internalStatus, 'paid') ? $paidAt : null,
                'dispensed_at'    => $this->hasReachedInternalStatus($internalStatus, 'dispensed') ? $dispensedAt : null,

                'created_at'      => $arrivedAt,
                'updated_at'      => $this->updatedAtForInternalStatus($internalStatus, $arrivedAt, $inProgressAt, $sentToPharmacyAt, $sentForPaymentAt, $paidAt, $dispensedAt, $finishedAt, $cancelledAt),
            ]);

            // 3. Buat Vital Sign
            VitalSign::create([
                'clinic_id' => 1,
                'outpatient_visit_id'    => $visit->id,
                'systole'     => rand(110, 130),
                'diastole'    => rand(70, 90),
                'temperature' => rand(36, 37) . '.' . rand(1, 9),
                'weight'      => rand(50, 70),
                'height'      => rand(150, 170),
                'satusehat_observation_blood_pressure_id' => (string) Str::uuid(),
                'satusehat_observation_temperature_id' => (string) Str::uuid(),
                'satusehat_observation_weight_id' => (string) Str::uuid(),
                'satusehat_observation_height_id' => (string) Str::uuid(),
                'created_at'  => $arrivedAt,
                'updated_at'  => $arrivedAt,
            ]);

            // 4. Buat Diagnosa
            $diagCount = $this->hasReachedInternalStatus($internalStatus, 'sent_to_pharmacy') ? rand(1, 3) : 0;

            for ($j = 0; $j < $diagCount; $j++) {
                $randomIcd = $icd10Stock->random();

                OutPatientDiagnosis::create([
                    'clinic_id'           => $visit->clinic_id,
                    'outpatient_visit_id' => $visit->id,
                    'icd10_code'          => $randomIcd->code,
                    'icd10_display'       => $randomIcd->name_en,
                    // Diagnosa pertama ($j == 0) selalu jadi Primary
                    'is_primary'          => ($j === 0),
                    'satusehat_condition_id' => (string) Str::uuid(),
                    'created_at'          => $visit->in_progress_at,
                    'updated_at'          => $visit->in_progress_at,
                ]);
            }

            // 5. Buat Resep & Hitung Total Harga Obat
            $numObat = $this->hasReachedInternalStatus($internalStatus, 'sent_to_pharmacy') ? rand(1, 3) : 0;
            for ($j = 0; $j < $numObat; $j++) {
                // Ambil obat random dari master (ID 1-10)
                $medicine = \App\Models\Medicine::inRandomOrder()->first();
                $qty = rand(1, 15);

                \App\Models\Prescription::create([
                    'clinic_id'           => $visit->clinic_id,
                    'outpatient_visit_id' => $visit->id,
                    'medicine_id'         => $medicine->id,
                    'medicine_name'       => $medicine->name, // Denormalisasi
                    'instruction'         => collect(['3 x 1 sesudah makan', '2 x 1 sebelum makan', '1 x 1 malam hari'])->random(),
                    'qty_ordered'         => $qty,
                    'qty_dispensed'       => $this->hasReachedInternalStatus($internalStatus, 'dispensed') ? $qty : 0,
                    'uom'                 => $medicine->uom ?? 'Tablet',

                    // Status Farmasi
                    'status'              => $this->prescriptionStatusForInternalStatus($internalStatus),

                    // Integrasi SATUSEHAT
                    'satusehat_medication_request_id' => (string) Str::uuid(),
                    'satusehat_medication_dispense_id' => $this->hasReachedInternalStatus($internalStatus, 'dispensed') ? (string) Str::uuid() : null,

                    // Timestamp TAT Farmasi (Sinkron dengan Visit)
                    'sent_to_pharmacy_at' => $visit->sent_to_pharmacy_at, // Jam dokter klik kirim
                    'sent_for_payment_at' => $visit->sent_for_payment_at, // Jam admin kirim tagihan
                    'paid_at'             => $visit->paid_at,             // Jam lunas
                    'dispensed_at'        => $visit->dispensed_at,        // Jam obat diserahkan ke pasien

                    'created_at'          => $visit->sent_to_pharmacy_at,
                    'updated_at'          => $visit->dispensed_at ?? $visit->paid_at ?? $visit->sent_for_payment_at ?? $visit->sent_to_pharmacy_at,
                ]);

                // Kalkulasi untuk Invoice (Menggunakan het_price seperti request sebelumnya)
                $totalMedicinePrice += ($medicine->het_price ?? 0) * $qty;
            }

            // 6. Buat Invoice (Fee Dokter + Total Obat)
            if ($this->hasReachedInternalStatus($internalStatus, 'sent_for_payment')) {
                Invoice::create([
                    'clinic_id'           => 1,
                    'outpatient_visit_id' => $visit->id,
                    'invoice_number'      => $invoiceNumber,
                    'registration_fee'    => $regFee,
                    'practitioner_fee'    => $practitionerFee,
                    'medicine_total'      => $totalMedicinePrice,
                    'grand_total'         => $regFee + $practitionerFee + $totalMedicinePrice,
                    'payment_status'      => $this->hasReachedInternalStatus($internalStatus, 'paid') ? 'paid' : 'unpaid',
                    'payment_method'      => $this->hasReachedInternalStatus($internalStatus, 'paid') ? collect(['cash', 'qris', 'transfer'])->random() : null,
                    'paid_at'             => $this->hasReachedInternalStatus($internalStatus, 'paid') ? $paidAt : null,
                    'created_at'          => $sentForPaymentAt,
                    'updated_at'          => $this->hasReachedInternalStatus($internalStatus, 'paid') ? $paidAt : $sentForPaymentAt,
                ]);
            }
        }
    }

    private function hasReachedInternalStatus(string $currentStatus, string $targetStatus): bool
    {
        if ($currentStatus === 'cancelled') {
            return false;
        }

        $flow = [
            'arrived',
            'at_practitioner',
            'sent_to_pharmacy',
            'sent_for_payment',
            'paid',
            'dispensed',
            'finished',
        ];

        return array_search($currentStatus, $flow, true) >= array_search($targetStatus, $flow, true);
    }

    private function visitStatusForInternalStatus(string $internalStatus): string
    {
        return match ($internalStatus) {
            'arrived' => 'arrived',
            'at_practitioner' => 'in-progress',
            'sent_to_pharmacy', 'sent_for_payment', 'paid', 'dispensed' => 'pharmacy',
            'cancelled' => 'cancelled',
            default => 'finished',
        };
    }

    private function prescriptionStatusForInternalStatus(string $internalStatus): string
    {
        return match ($internalStatus) {
            'sent_to_pharmacy' => 'sent_to_pharmacy',
            'sent_for_payment' => 'sent_for_payment',
            'paid' => 'paid',
            'dispensed', 'finished' => 'dispensed',
            default => 'draft',
        };
    }

    private function updatedAtForInternalStatus(
        string $internalStatus,
        Carbon $arrivedAt,
        Carbon $inProgressAt,
        Carbon $sentToPharmacyAt,
        Carbon $sentForPaymentAt,
        Carbon $paidAt,
        Carbon $dispensedAt,
        Carbon $finishedAt,
        Carbon $cancelledAt
    ): Carbon {
        return match ($internalStatus) {
            'arrived' => $arrivedAt,
            'at_practitioner' => $inProgressAt,
            'sent_to_pharmacy' => $sentToPharmacyAt,
            'sent_for_payment' => $sentForPaymentAt,
            'paid' => $paidAt,
            'dispensed' => $dispensedAt,
            'cancelled' => $cancelledAt,
            default => $finishedAt,
        };
    }
}

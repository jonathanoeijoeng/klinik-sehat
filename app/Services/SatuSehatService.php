<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\OutpatientVisit;


class SatuSehatService
{
    protected $baseUrl;
    protected $authUrl;
    protected $clientId;
    protected $clientSecret;
    protected $organizationId;
    protected $orgSatusehatId;
    protected $kfaBaseUrl = 'https://api-satusehat-stg.kemkes.go.id/kfa-v2';

    /**
     * Create a new service instance.
     */

    public function __construct()
    {
        $this->baseUrl = config('services.satusehat.base_url');
        $this->authUrl = config('services.satusehat.auth_url');
        $this->clientId = config('services.satusehat.client_id');
        $this->clientSecret = config('services.satusehat.client_secret');
        $this->organizationId = config('services.satusehat.org_id');

        $this->orgSatusehatId = Organization::find(1)->satusehat_id;
    }

    public function post($resource, $payload)
    {
        $token = $this->getToken();

        if (!$token) {
            Log::error("SATUSEHAT Token tidak ditemukan saat mencoba post ke $resource");
            return false;
        }

        return Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->baseUrl . '/' . $resource, $payload);
    }

    /**
     * Ambil Access Token dengan Cache 50 menit (3000 detik)
     */
    public function getToken()
    {
        return Cache::remember('satusehat_access_token', 3000, function () {
            $response = Http::asForm()->post($this->authUrl . '/accesstoken?grant_type=client_credentials', [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->failed()) {
                Log::error('SATUSEHAT Auth Failed: ' . $response->body());
                return null;
            }

            return $response->json('access_token');
        });
    }

    /**
     * Create Encounter (Rawat Jalan / AMB)
     */
    public function createEncounter($visit)
    {
        $token = $this->getToken();

        if (!$token) {
            return false;
        }

        $payload = [
            'resourceType' => 'Encounter',
            'status' => 'arrived', // Status awal kedatangan pasien
            'class' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => 'AMB', // Ambulatory (Rawat Jalan)
                'display' => 'ambulatory'
            ],
            'subject' => [
                'reference' => 'Patient/' . $visit->patient->satusehat_id,
                'display' => $visit->patient->name
            ],
            'participant' => [
                [
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                    'code' => 'ATND',
                                    'display' => 'attender'
                                ]
                            ]
                        ]
                    ],
                    'individual' => [
                        'reference' => 'Practitioner/' . $visit->practitioner->satusehat_id,
                        'display' => $visit->practitioner->name
                    ]
                ]
            ],
            'period' => [
                'start' => $visit->arrived_at->toIso8601String() // Format: 2026-04-12T19:30:00+07:00
            ],
            'location' => [
                [
                    'location' => [
                        'reference' => 'Location/' . $visit->location->satusehat_id,
                        'display' => $visit->location->name
                    ]
                ]
            ],
            'statusHistory' => [
                [
                    'status' => 'arrived',
                    'period' => [
                        'start' => $visit->arrived_at->toIso8601String()
                    ]
                ]
            ],
            'serviceProvider' => [
                'reference' => 'Organization/' . $this->organizationId
            ],
            'identifier' => [
                [
                    'system' => 'http://sys-ids.kemkes.go.id/encounter/' . $this->orgSatusehatId,
                    'value' => $visit->visit_number // ID unik lokal Anda (KS-xxxxxx)
                ]
            ]
        ];

        return $this->post('Encounter', $payload);
    }

    // app/Services/SatuSehatService.php

    public function createObservationBloodPressure(OutpatientVisit $visit)
    {
        $vitalSign = $visit->vitalSign; // Pastikan relasi ini ada

        $payload = [
            "resourceType" => "Observation",
            "status" => "final",
            "category" => [
                [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                            "code" => "vital-signs",
                            "display" => "Vital Signs"
                        ]
                    ]
                ]
            ],
            "code" => [
                "coding" => [
                    [
                        "system" => "http://loinc.org",
                        "code" => "85354-9",
                        "display" => "Blood pressure panel with all children optional"
                    ]
                ]
            ],
            "subject" => [
                "reference" => "Patient/" . $visit->patient->satusehat_id,
                "display" => $visit->patient->name
            ],
            "encounter" => [
                "reference" => "Encounter/" . $visit->satusehat_encounter_id,
                "display" => "Pemeriksaan fisik awal pasien " . $visit->patient->name
            ],
            "performer" => [
                [
                    "reference" => "Practitioner/" . $visit->practitioner->satusehat_id,
                    "display" => $visit->practitioner->name
                ]
            ],
            "effectiveDateTime" => $visit->arrived_at->toIso8601String(),
            "issued" => now()->toIso8601String(),
            "component" => [
                [
                    "code" => [
                        "coding" => [
                            [
                                "system" => "http://loinc.org",
                                "code" => "8480-6",
                                "display" => "Systolic blood pressure"
                            ]
                        ]
                    ],
                    "valueQuantity" => [
                        "value" => (int) $vitalSign->systole,
                        "unit" => "mm[Hg]",
                        "system" => "http://unitsofmeasure.org",
                        "code" => "mm[Hg]"
                    ]
                ],
                [
                    "code" => [
                        "coding" => [
                            [
                                "system" => "http://loinc.org",
                                "code" => "8462-4",
                                "display" => "Diastolic blood pressure"
                            ]
                        ]
                    ],
                    "valueQuantity" => [
                        "value" => (int) $vitalSign->diastole,
                        "unit" => "mm[Hg]",
                        "system" => "http://unitsofmeasure.org",
                        "code" => "mm[Hg]"
                    ]
                ]
            ]
        ];

        return $this->post('Observation', $payload);
    }

        /**
     * Helper untuk mengirim observasi tunggal (Weight, Height, Temp, etc)
     */
    public function createSimpleObservation(OutpatientVisit $visit, $code, $display, $value, $unit, $unitCode)
    {
        $payload = [
            "resourceType" => "Observation",
            "status" => "final",
            "category" => [
                [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                            "code" => "vital-signs",
                            "display" => "Vital Signs"
                        ]
                    ]
                ]
            ],
            "code" => [
                "coding" => [
                    [
                        "system" => "http://loinc.org",
                        "code" => $code,
                        "display" => $display
                    ]
                ]
            ],
            "subject" => [
                "reference" => "Patient/" . $visit->patient->satusehat_id,
                "display" => $visit->patient->name
            ],
            "encounter" => [
                "reference" => "Encounter/" . $visit->satusehat_encounter_id,
                "display" => "Pemeriksaan fisik awal pasien " . $visit->patient->name
            ],
            "performer" => [
                [
                    "reference" => "Practitioner/" . $visit->practitioner->satusehat_id,
                    "display" => $visit->practitioner->name
                ]
            ],
            "effectiveDateTime" => $visit->arrived_at->toIso8601String(),
            "issued" => now()->toIso8601String(),
            "valueQuantity" => [
                "value" => (float) $value,
                "unit" => $unit,
                "system" => "http://unitsofmeasure.org",
                "code" => $unitCode
            ]
        ];

        return $this->post('Observation', $payload);
    }

    // app/Services/SatuSehatService.php

    public function searchKfa($keyword)
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->get("https://api-satusehat-stg.dto.kemkes.go.id/kfa-v2/products/all", [
                'keyword' => $keyword,
                'product_type' => 'obat', // Pastikan huruf kecil
                'page' => 1,
                'size' => 10
            ]);

        if ($response->successful()) {
            $json = $response->json();
            // Sesuai dd() kamu: items -> data
            return $json['items']['data'] ?? [];
        }
        return [];
    }

    public function updateEncounterDiagnosis($visit)
    {
        $token = $this->getToken();
        $encounterId = $visit->satusehat_encounter_id;

        // Ambil data Encounter yang sudah ada dulu dari SatuSehat
        $currentEncounter = Http::withToken($token)
            ->get(config('services.satusehat.base_url') . "/Encounter/{$encounterId}")
            ->json();

        // Map diagnosa dari DB lokal ke format FHIR
        $diagnosisPayload = $visit->diagnoses->map(function ($diag, $index) {
            return [
                "condition" => [
                    "reference" => "Condition/" . $diag->satusehat_condition_id, // Jika Condition sudah di-POST
                    "display" => $diag->name_en
                ],
                "use" => [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/diagnosis-role",
                            "code" => ($index === 0) ? "pre-op" : "post-op", // Sesuaikan mapping role-nya
                            "display" => ($index === 0) ? "Primary Diagnosis" : "Secondary Diagnosis"
                        ]
                    ]
                ],
                "rank" => $index + 1
            ];
        })->toArray();

        // Masukkan ke payload Encounter
        $currentEncounter['diagnosis'] = $diagnosisPayload;

        // Update Encounter di SatuSehat
        $response = Http::withToken($token)
            ->put(config('services.satusehat.base_url') . "/Encounter/{$encounterId}", $currentEncounter);

        return $response->json();
    }

    public function sendCondition($diagnosis, $visit)
    {
        $token = $this->getToken();
        
        $payload = [
            "resourceType" => "Condition",
            "clinicalStatus" => [
                "coding" => [
                    [
                        "system" => "http://terminology.hl7.org/CodeSystem/condition-clinical",
                        "code" => "active",
                        "display" => "Active"
                    ]
                ]
            ],
            "category" => [
                [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/condition-category",
                            "code" => "encounter-diagnosis",
                            "display" => "Encounter Diagnosis"
                        ]
                    ]
                ]
            ],
            "code" => [
                "coding" => [
                    [
                        "system" => "http://hl7.org/fhir/sid/icd-10",
                        "code" => $diagnosis->icd10_code,
                        "display" => $diagnosis->name_en
                    ]
                ]
            ],
            "subject" => [
                "reference" => "Patient/" . $visit->patient->satusehat_id,
                "display" => $visit->patient->name
            ],
            "encounter" => [
                "reference" => "Encounter/" . $visit->satusehat_encounter_id,
                "display" => "Kunjungan Rawat Jalan"
            ],
            "recordedDate" => now()->toIso8601String(),
        ];

        $response = Http::withToken($token)
            ->post(config('services.satusehat.base_url') . '/Condition', $payload);

        return $response->json();
    }

        public function sendPrescription($prescription, $visit)
{
    $token = $this->getToken();

    $payload = [
        "resourceType" => "MedicationRequest",
        "status" => "completed", // Sesuaikan jika sudah diberikan bisa 'completed'
        "intent" => "order",
        "priority" => "routine",
        "identifier" => [
            [
                "system" => "http://sys-ids.kemkes.go.id/prescription/" . $this->orgSatusehatId,
                "use" => "official",
                "value" => "PRES-" . $prescription->id
            ],
            [
                "system" => "http://sys-ids.kemkes.go.id/prescription-item/" . $this->orgSatusehatId,
                "use" => "official",
                "value" => "PRES-" . $prescription->id . "-1"
            ]
        ],
        "category" => [
            [
                "coding" => [
                    [
                        "system" => "http://terminology.hl7.org/CodeSystem/medicationrequest-category",
                        "code" => "outpatient",
                        "display" => "Outpatient"
                    ]
                ]
            ]
        ],
        "medicationReference" => [
            // Jika Anda sudah POST Medication sebelumnya, gunakan ID-nya di sini
            // Tapi jika belum, kita tetap bisa pakai teknik #contained dengan struktur detail ini
            "reference" => "Medication/" . $prescription->medicine->satusehat_medication_id, 
            "display" => $prescription->medicine_name
        ],
        "subject" => [
            "reference" => "Patient/" . $visit->patient->satusehat_id,
            "display" => $visit->patient->name
        ],
        "encounter" => [
            "reference" => "Encounter/" . $visit->satusehat_encounter_id
        ],
        "authoredOn" => $prescription->created_at->format('Y-m-d'), // Contoh Anda pakai format date saja
        "requester" => [
            "reference" => "Practitioner/" . $visit->practitioner->satusehat_id,
            "display" => $visit->practitioner->name
        ],
        "reasonCode" => [
            [
                "coding" => [
                    [
                        "system" => "http://hl7.org/fhir/sid/icd-10",
                        // Di contoh Anda, alasan (diagnosa) juga dikirimkan di sini
                        "code" => $visit->diagnoses->first()->icd10_code ?? 'Z00.0',
                        "display" => $visit->diagnoses->first()->name_en ?? 'General Examination'
                    ]
                ]
            ]
        ],
        "dosageInstruction" => [
            [
                "sequence" => 1,
                "text" => $prescription->instruction,
                "timing" => [
                    "repeat" => [
                        "frequency" => 1,
                        "period" => 1,
                        "periodUnit" => "d"
                    ]
                ],
                "route" => [
                    "coding" => [
                        [
                            "system" => "http://www.whocc.no/atc",
                            "code" => "O",
                            "display" => "Oral"
                        ]
                    ]
                ],
                "doseAndRate" => [
                    [
                        "type" => [
                            "coding" => [
                                [
                                    "system" => "http://terminology.hl7.org/CodeSystem/dose-rate-type",
                                    "code" => "ordered",
                                    "display" => "Ordered"
                                ]
                            ]
                        ],
                        "doseQuantity" => [
                            "value" => (float) $prescription->quantity,
                            "unit" => $prescription->uom ?? "TAB",
                            "system" => "http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm",
                            "code" => $prescription->uom ?? "TAB"
                        ]
                    ]
                ]
            ]
        ],
        "dispenseRequest" => [
            "quantity" => [
                "value" => (float) $prescription->quantity,
                "unit" => $prescription->uom ?? "TAB",
                "system" => "http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm",
                "code" => $prescription->uom ?? "TAB"
            ],
            "performer" => [
                "reference" => "Organization/" . $this->organizationId,
            ]
        ]
    ];

    return Http::withToken($token)
        ->post(config('services.satusehat.base_url') . '/MedicationRequest', $payload)
        ->json();
        if ($response->status() === 400) {
            \Log::error('SatuSehat 400 Payload:', $payload);
            \Log::error('SatuSehat 400 Response:', $response->json() ?? ['raw' => $response->body()]);
        }
        
        return $response->json();
}

        // DEBUG: Jika masih 400, kita intip raw body-ny
}
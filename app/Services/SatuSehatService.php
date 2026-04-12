<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SatuSehatService
{
    protected $baseUrl;
    protected $authUrl;
    protected $clientId;
    protected $clientSecret;
    protected $organizationId;
    protected $orgSatusehatId;

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

        return Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->baseUrl . '/Encounter', $payload);
    }
}
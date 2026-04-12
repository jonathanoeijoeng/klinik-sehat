<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat pasien yang ada di resource satusehat
        Patient::create([
            'satusehat_id' => 'P02280547535',
            'nik' => '9104025209000006',
            'name' => 'Salsabilla Anjani Rizki',
            'gender' => 'female',
            'birth_date' => '2001-04-16',
            'phone_number' => '1234567890',
            'address' => 'Bandung',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '110101',
            'village_code' => '110101001',
            'last_sync_at' => now(),
        ]);

    }
}

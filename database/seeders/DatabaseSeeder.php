<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            ClinicSeeder::class,
            OrganizationSeeder::class,
            PatientSeeder::class,
            LocationSeeder::class,
            Icd10sSeeder::class,
            MedicinesTableSeeder::class,
            PractitionersTableSeeder::class,
        ]);

        User::create([
            'name' => 'Jonathan',
            'email' => 'jonathan.oeijoeng@gmail.com',
            'password' => bcrypt('password'),
            'clinic_id' => 1
        ]);

        $this->call(OutpatientVisitSeeder::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Patient;
use App\Models\Practitioner;
use App\Models\Location;
use App\Models\VitalSign;
use App\Models\OutPatientDiagnosis;
use App\Models\Invoice;
use App\Models\Prescription;


class OutpatientVisit extends Model
{
    protected $table = 'outpatient_visits';
    // protected $fillable = [
    //     'patient_id',
    //     'practitioner_id',
    //     'location_id',
    //     'visit_date',
    //     'visit_type',
    //     'visit_reason',
    //     'status',
    //     'created_at',
    //     'updated_at',
    //     'sync_status',
    //     'satusehat_encounter_id',
    //     'complaint',
    //     'systole',
    //     'diastole',
    //     'weight',
    //     'temperature',
    //     'arrived_at',
    //     'in_progress_at',
    //     'finished_at',
    //     'cancelled_at',
    // ];

    protected $guarded = [];

    public function isSynced(): bool
    {
        return !is_null($this->satusehat_encounter_id);
    }

    public function scopePendingSync($query)
    {
        return $query->where('sync_status', 'pending');
    }

    // app/Models/OutpatientVisit.php
    public function invoice()
    {
        // Gunakan hasOne jika 1 kunjungan hanya punya 1 invoice
        return $this->hasOne(Invoice::class, 'visit_id'); 
    }

    // app/Models/OutpatientVisit.php
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // app/Models/OutpatientVisit.php
    public function practitioner()
    {
        return $this->belongsTo(Practitioner::class);
    }

    // app/Models/OutpatientVisit.php
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function vitalSign()
    {
        return $this->hasOne(VitalSign::class, 'visit_id');
    }

    protected $casts = [
        'arrived_at' => 'datetime', // Ini kuncinya!
    ];

    // App/Models/OutpatientVisit.php
    public function diagnoses()
    {
        // Gunakan outpatient_visit_id sesuai nama kolom di tabel out_patient_diagnoses
        return $this->hasMany(OutpatientDiagnosis::class, 'outpatient_visit_id')
                    ->orderBy('is_primary', 'desc') // True (1) akan di atas False (0)
                    ->orderBy('created_at', 'desc'); // Yang terbaru di atas jika sama-sama sekunder
    }

    public function prescriptions()
    {
        // Pastikan foreign key sesuai dengan yang kamu buat di migrasi
        return $this->hasMany(Prescription::class, 'outpatient_visit_id')
                    ->latest(); // Supaya obat yang baru diinput ada di atas
    }

    

}

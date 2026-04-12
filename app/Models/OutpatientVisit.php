<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}

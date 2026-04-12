<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutpatientVisit extends Model
{
    protected $table = 'outpatient_visits';
    protected $fillable = [
        'patient_id',
        'practitioner_id',
        'location_id',
        'visit_date',
        'visit_type',
        'visit_reason',
        'status',
        'created_at',
        'updated_at',
    ];

    public function isSynced(): bool
    {
        return !is_null($this->satusehat_encounter_id);
    }

    public function scopePendingSync($query)
    {
        return $query->where('sync_status', 'pending');
    }
}

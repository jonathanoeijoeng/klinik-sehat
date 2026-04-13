<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutPatientDiagnosis extends Model
{
    protected $table = 'out_patient_diagnoses';
    protected $guarded = [];

    public function visit()
    {
        return $this->belongsTo(OutPatientVisit::class);
    }
    public function icd10()
    {
        return $this->belongsTo(Icd10::class);
    }
}

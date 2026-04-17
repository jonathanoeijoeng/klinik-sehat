<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VitalSign extends Model
{
    protected $guarded = [];

    public function outpatient_visit()
    {
        return $this->belongsTo(OutpatientVisit::class);
    }
    protected $table = 'vital_signs';
}

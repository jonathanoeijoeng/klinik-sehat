<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\OutpatientVisit;

class Invoice extends Model
{
    protected $guarded = [];

    public function outpatient_visit()
    {
        return $this->belongsTo(OutpatientVisit::class, 'outpatient_visit_id');
    }
}

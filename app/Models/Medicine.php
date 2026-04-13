<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $table = 'medicines';
    protected $guarded = [];

    public function prescriptions()
{
    return $this->hasMany(Prescription::class);
}
}

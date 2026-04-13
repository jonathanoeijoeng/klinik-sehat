<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescriptions';
    protected $guarded = [];

    public function medicine()
    {
        // Pastikan foreign_key di tabel prescriptions bernama medicine_id
        return $this->belongsTo(Medicine::class);
    }
}

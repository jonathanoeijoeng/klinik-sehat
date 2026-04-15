<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory;

    protected $fillable = [
        'satusehat_id',
        'nik',
        'name',
        'gender',
        'birth_date',
        'phone_number',
        'address',
        'province_code',
        'city_code',
        'district_code',
        'village_code',
        'last_sync_at',
    ];

    public function getAgeAttribute()
    {
        return \Carbon\Carbon::parse($this->birth_date)->age;
    }

    public function getAgeStringAttribute()
    {
        $diff = \Carbon\Carbon::parse($this->birth_date)->diff(now());
        return "{$diff->y} th, {$diff->m} bln, {$diff->d} hr";
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Town extends Model
{
    use HasFactory;

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function addresses()
    {
        return $this->hasMany('App\Models\Address');
    }
}

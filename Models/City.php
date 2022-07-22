<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    public function towns()
    {
        return $this->hasMany('App\Models\Town');
    }

    public function technicians()
    {
        return $this->hasMany('App\Models\Technician');
    }

    public function addresses()
    {
        return $this->hasMany('App\Models\Address');
    }

    public function projects()
    {
        return $this->hasMany('App\Models\Project');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    public function proficiency()
    {
        return $this->belongsTo('App\Models\Proficiency');
    }

    public function technicians()
    {
        return $this->belongsToMany('App\Models\Technician');
    }

    public function projects()
    {
        return $this->belongsToMany('App\Models\Project');
    }

    public function products()
    {
        return $this->belongsToMany('App\Models\Product');
    }
}

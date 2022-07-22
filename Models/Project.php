<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    public function skills()
    {
        return $this->belongsToMany('App\Models\Skill', 'project_skill');
    }

    public function products()
    {
        return $this->belongsToMany('App\Models\Product');
    }

    public function incomes()
    {
        return $this->hasMany('App\Models\Income');
    }

    public function proficiency()
    {
        return $this->belongsTo('App\Models\Proficiency');
    }

    public function technician()
    {
        return $this->belongsTo('App\Models\Technician');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function photos()
    {
        return $this->hasMany('App\Models\Photo');
    }

    public function address()
    {
        return $this->belongsTo('App\Models\Address');
    }

    public function bids()
    {
        return $this->hasMany('App\Models\Bid');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function bankInformation()
    {
        return $this->belongsTo('App\Models\BankInformation');
    }

    public function proficiency()
    {
        return $this->belongsTo('App\Models\Proficiency');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function projects()
    {
        return $this->hasMany('App\Models\Project');
    }

    public function incomes()
    {
        return $this->hasMany('App\Models\Income');
    }

    public function certificates()
    {
        return $this->hasMany('App\Models\Certificate');
    }

    // client has been saved
    public function clients()
    {
        return $this->belongsToMany('App\Models\Client');
    }

    public function skills()
    {
        return $this->belongsToMany('App\Models\Skill', 'technician_skill');
    }

    public function bids()
    {
        return $this->hasMany('App\Models\Bid');
    }
}

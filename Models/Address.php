<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'lat', 'long'];

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function town()
    {
        return $this->belongsTo('App\Models\Town');
    }

    public function projects()
    {
        return $this->hasMany('App\Models\Project');
    }
}

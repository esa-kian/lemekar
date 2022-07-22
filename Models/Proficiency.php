<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proficiency extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

    public function technicians()
    {
        return $this->hasMany('App\Models\Technician');
    }

    public function projects()
    {
        return $this->hasMany('App\Models\Project');
    }

    public function skills()
    {
        return $this->hasMany('App\Models\Skill');
    }

    public function discountCodes()
    {
        return $this->hasMany('App\Models\DiscountCode');
    }
}

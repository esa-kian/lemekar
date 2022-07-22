<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function registeredCodes()
    {
        return $this->hasMany('App\Models\RegisteredCode');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    public function voter()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function voted()
    {
        return $this->belongsTo('App\Models\User');
    }
}

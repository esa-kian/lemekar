<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    public function proficiency()
    {
        return $this->belongsTo('App\Models\Proficiency');
    }
}

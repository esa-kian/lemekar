<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankInformation extends Model
{
    use HasFactory;

    public function technician()
    {
        return $this->hasOne('App\Models\Technician');
    }

    public function client()
    {
        return $this->hasOne('App\Models\Client');
    }
}

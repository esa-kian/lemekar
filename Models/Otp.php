<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = ['otp', 'phone_number', 'expire_at'];
    
    use HasFactory;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisteredCode extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function discountCode()
    {
        return $this->belongsTo('App\Models\DiscountCode');
    }

    public function inviteCode()
    {
        return $this->belongsTo('App\Models\InviteCode');
    }
}

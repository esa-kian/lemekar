<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'amount'];
    
    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}

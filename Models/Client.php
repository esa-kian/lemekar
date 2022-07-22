<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'birthdate',
        'gender',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function bankInformation()
    {
        return $this->belongsTo('App\Models\BankInformation');
    }
    
    public function withdrawRequests()
    {
        return $this->hasMany('App\Models\WithdrawRequest');
    }

    public function addresses()
    {
        return $this->hasMany('App\Models\Address');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    public function projects()
    {
        return $this->hasMany('App\Models\Project');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    // saved technicians
    public function technicians()
    {
        return $this->belongsToMany('App\Models\Technician');
    }
}

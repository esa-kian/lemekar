<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function registeredCodes()
    {
        return $this->hasMany('App\Models\RegisteredCode');
    }

    public function messages()
    {
        return $this->hasMany('App\Models\Message');
    }

    public function rates()
    {
        return $this->hasMany('App\Models\Rate');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification');
    }

    public function technician()
    {
        return $this->hasOne('App\Models\Technician');
    }

    public function client()
    {
        return $this->hasOne('App\Models\Client');
    }
}

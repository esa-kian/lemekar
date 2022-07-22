<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'descrption'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}

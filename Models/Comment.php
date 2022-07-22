<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    public function project()
    {
        return $this->belongsTo('App\Models\Project');
    }

    public function technician()
    {
        return $this->belongsTo('App\Models\Technician');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRelation extends Model
{
    use HasFactory;

    public function from()
    {
        return $this->belongsTo(User::class, 'user_from', 'id');
    }

    public function to()
    {
        return $this->belongsTo(User::class, 'user_to', 'id');
    }
}

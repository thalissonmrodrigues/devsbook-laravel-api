<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'id_post');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'id_post');
    }
}

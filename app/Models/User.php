<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $hidden = [
        'password',
        'token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'id_user');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'id_user');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'id_user');
    }

    public function following()
    {
        return $this->hasMany(UserRelation::class, 'user_from');
    }

    public function followers()
    {
        return $this->hasMany(UserRelation::class, 'user_to');
    }
}

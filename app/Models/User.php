<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'role',
        'google_id',
        'google_drive_folder_id',
        'google_token',
        'google_refresh_token',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'last_login',
    ];

    protected $hidden = [
        'google_token',
        'google_refresh_token',
    ];

    public function getRoleAttribute($value)
    {
        return ucfirst($value);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? $this->avatar : 'default-url';
    }
}

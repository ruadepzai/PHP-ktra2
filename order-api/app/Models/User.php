<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Lấy định danh lưu trữ trong JWT claim.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Trả về mảng chứa các custom claims bổ sung.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Mối quan hệ: Một User có nhiều Đơn hàng.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

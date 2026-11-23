<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model implements AuthenticatableContract
{
    use HasFactory, HasApiTokens, Authenticatable;

    protected $table = 'customers';
    protected $guarded = ['id'];

    protected $fillable = [
        'nama', 'email', 'password', 'no_hp', 'alamat', 'foto', 'google_id',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'must_change_password'     => 'boolean',
        'password_temp_expires_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

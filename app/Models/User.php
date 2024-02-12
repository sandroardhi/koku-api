<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'level',
        'profile_picture',
        'nomor_hp',
        'deskripsi',
        'pengantarIsAvailable',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function kantin(): HasOne
    {
        return $this->hasOne(Kantin::class, "penjual_id");
    }
    public function keranjang()
    {
        return $this->hasOne(Keranjang::class, 'pembeli_id');
    }
    public function tujuans()
    {

        return $this->hasMany(Tujuan::class);
    }
    public function assignedOrders()
    {
        return $this->hasMany(Order::class, 'pengantar_id');
    }
}

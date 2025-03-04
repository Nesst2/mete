<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nama',
        'tanggal_lahir',
        'nomor_hp',
        'alamat',
        'role',
        'daerah_id',
        'username',
        'password',
        'email'
    ];

    protected $hidden = [
        'password',
    ];

    public function daerah() {
        return $this->belongsTo(Daerah::class);
    }

    public function vendorDeactivationRequests() {
        return $this->hasMany(VendorDeactivationRequest::class, 'sales_id');
    }

    public function approvedDeactivationRequests() {
        return $this->hasMany(VendorDeactivationRequest::class, 'admin_id');
    }
}


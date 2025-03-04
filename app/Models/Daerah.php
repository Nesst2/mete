<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daerah extends Model {
    use HasFactory;

    protected $table = 'daerah';

    protected $fillable = [
        'kota',
        'provinsi'
    ];

    public function wilayah() {
        return $this->hasMany(Wilayah::class, 'kota', 'kota');
    }

    public function users() {
        return $this->hasMany(User::class);
    }

    public function vendors() {
        return $this->hasMany(Vendor::class);
    }
}

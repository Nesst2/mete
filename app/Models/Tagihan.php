<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';

    protected $fillable = [
        'vendor_id',
        'uang_masuk',
         'daerah_id',
        'tanggal_masuk',
        'status_kunjungan',
        'kunjungan_ke',
        'tagihan_id',
    ];

    // Pastikan tanggal masuk menjadi objek Carbon
    protected $dates = ['tanggal_masuk'];  // Menambahkan tanggal_masuk ke sini

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function daerah()
    {
        return $this->belongsTo(Daerah::class);
    }

    public function retur()
    {
        return $this->hasOne(Retur::class, 'tagihan_id');
    }


}



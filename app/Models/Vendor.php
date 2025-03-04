<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model {
    use HasFactory;

    protected $table = 'vendors';

    protected $fillable = [
        'kode_vendor',
        'nama',
        'keterangan',
        'jam_operasional',
        'nomor_hp',
        'location_link',
        'gambar_vendor',
        //'daerah_id',
        'status'
    ];
    
    // Di App\Models\Vendor.php
    public function getStatusLabelAttribute()
    {
        $labels = [
            'aktif'               => 'Aktif',
            'nonaktif'            => 'Nonaktif',
            'diblokir'            => 'Diblokir',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }
    
    public function wilayah()
    {
    return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function daerah()
    {
        return $this->belongsTo(Daerah::class);  // Jika ada kolom daerah_id
    }

    public function tagihan() {
        return $this->hasMany(Tagihan::class);
    }

    public function retur() {
        return $this->hasMany(Retur::class);
    }

    public function deactivationRequests() {
        return $this->hasMany(VendorDeactivationRequest::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retur extends Model {
    use HasFactory;

    protected $table = 'retur';

    protected $fillable = [
        'vendor_id',
        'nominal_debet',
        'jumlah_retur',
        'keterangan',
        'tagihan_id',
    ];

    public function vendor() {
        return $this->belongsTo(Vendor::class);
    }

    public function tagihan() {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
    
}


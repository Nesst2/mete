<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model {
    use HasFactory;

    protected $table = 'wilayah';

    protected $fillable = [
        'nama',
        'kota'
    ];

    public function daerah() {
        return $this->belongsTo(Daerah::class, 'kota', 'kota');
    }

    // app/Models/Wilayah.php

    public function vendors() {
        return $this->hasMany(Vendor::class);  // Wilayah memiliki banyak vendor
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDeactivationRequest extends Model {
    use HasFactory;

    protected $table = 'vendor_deactivation_requests';

    protected $fillable = [
        'vendor_id',
        'sales_id',
        'reason',
        'status',
        'admin_id',
        'approved_at'
    ];

    public function vendor() {
        return $this->belongsTo(Vendor::class);
    }

    public function sales() {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function admin() {
        return $this->belongsTo(User::class, 'admin_id');
    }
}


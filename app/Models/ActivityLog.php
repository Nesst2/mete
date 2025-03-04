<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;


class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'table_name',
        'record_id',
        'action',
        'old_data',
        'new_data',
        'description',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Simpan log dengan metode static
    public static function log($action, $table, $recordId, $oldData = null, $newData = null, $description = null)
    {
        return self::create([
            'user_id' => Auth::id(),
            'table_name' => $table,
            'record_id'  => $recordId,
            'action'     => $action,
            'old_data'   => $oldData,
            'new_data'   => $newData,
            'description' => $description,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::header('User-Agent'),
        ]);
    }
}

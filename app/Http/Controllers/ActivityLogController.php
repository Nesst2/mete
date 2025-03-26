<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Mulai query log aktivitas
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Cek apakah parameter date_filter ada dan tidak kosong
        if ($request->filled('date_filter')) {
            // Gunakan whereDate agar hanya membandingkan bagian tanggal (tanpa waktu)
            $query->whereDate('created_at', $request->input('date_filter'));
        }

        // Lakukan pagination, misalnya 5 data per halaman
        $logs = $query->paginate(5);

        return view('log_activity.index', compact('logs'));
    }
}

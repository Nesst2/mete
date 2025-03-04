<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {   
        $logs = \App\Models\ActivityLog::with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(5);

        return view('log_activity.index', compact('logs'));
    }


}


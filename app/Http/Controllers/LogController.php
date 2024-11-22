<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = Log::orderBy('created_at', 'desc')->limit(10)->get();

        $formattedLogs = $logs->map(function ($log) {
            $log->formatted_created_at = Carbon::parse($log->created_at)->format('d-m-Y H:i:s');
            return $log;
        });
    
        return response([
            'data' => $formattedLogs
        ]);
    }

    public function newLogs(Request $request)
    {
        $formattedLogs = [];
        $logs = Log::where('id', '>', $request->lastId)->orderBy('created_at', 'desc')->get();

        $formattedLogs = $logs->map(function ($log) {
            $log->formatted_created_at = Carbon::parse($log->created_at)->format('d-m-Y H:i:s');
            return $log;
        });

        return response([
            'data' => $formattedLogs
        ]);
    }
}

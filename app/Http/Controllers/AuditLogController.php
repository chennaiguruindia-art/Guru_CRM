<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        $logs = $query->paginate(25)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $logs]);
        }

        return view('audit-logs.index', compact('logs'));
    }
}

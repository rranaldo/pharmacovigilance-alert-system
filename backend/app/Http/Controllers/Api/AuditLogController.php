<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action'    => 'nullable|string|max:100',
            'user_id'   => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'per_page'  => 'nullable|integer|min:10|max:100',
        ]);

        $query = AuditLog::with('user')->latest();

        if (!empty($validated['action'])) {
            $query->where('action', 'like', '%' . $validated['action'] . '%');
        }

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $logs = $query->paginate($validated['per_page'] ?? 25);

        return response()->json($logs);
    }
}

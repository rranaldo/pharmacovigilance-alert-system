<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Order;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ExportService $exportService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lot' => 'required|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $orders = Order::withLotNumber($validated['lot'])
            ->inDateRange($validated['start_date'] ?? null, $validated['end_date'] ?? null)
            ->with(['customer', 'items.medication'])
            ->withCount('alerts')
            ->orderBy('purchase_date', 'desc')
            ->paginate($validated['per_page'] ?? 15);

        return response()->json($orders);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with([
            'customer',
            'items.medication',
            'alerts' => fn($q) => $q->latest(),
        ])->findOrFail($id);

        return $this->success($order);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'lot' => 'required|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        return $this->exportService->exportOrdersCsv(
            $validated['lot'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );
    }
}

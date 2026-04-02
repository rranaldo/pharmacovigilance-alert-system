<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationSearchRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;

class MedicationController extends Controller
{
    use ApiResponse;

    public function search(MedicationSearchRequest $request): JsonResponse
    {
        $startDate = $request->start_date ?? now()->subDays(30)->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $medications = Medication::byLot($request->lot)
            ->withCount(['orderItems as affected_orders_count' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('order', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('purchase_date', [$startDate, $endDate]);
                });
            }])
            ->get();

        return $this->success([
            'medications' => $medications,
            'filters' => [
                'lot' => $request->lot,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ], 'Search completed');
    }
}

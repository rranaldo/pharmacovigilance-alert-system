<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendAlertRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Alert;
use App\Models\Customer;
use App\Models\Order;
use App\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AlertService $alertService
    ) {}

    public function send(SendAlertRequest $request): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($request->customer_id);
            $order = Order::findOrFail($request->order_id);

            $alert = $this->alertService->sendAlert(
                $customer,
                $order,
                $request->user(),
                $request->lot_number,
                $request->message
            );

            return $this->success(
                $alert->load(['customer', 'order']),
                'Alert sent successfully'
            );
        } catch (\Exception $e) {
            report($e);

            return $this->error('Failed to deliver the alert. A failure notification has been sent to the system administrator.', 500);
        }
    }

    public function sendBulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_ids' => 'required|array|min:1|max:200',
            'customer_ids.*' => 'integer|exists:customers,id',
            'lot_number' => 'required|string|max:50',
            'message' => 'nullable|string|max:1000',
        ]);

        $results = $this->alertService->sendBulkAlerts(
            $validated['customer_ids'],
            $validated['lot_number'],
            $request->user(),
            $validated['message'] ?? null
        );

        $message = sprintf(
            'Bulk send completed: %d sent, %d failed, %d skipped',
            $results['sent'],
            $results['failed'],
            $results['skipped']
        );

        return $this->success($results, $message);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lot_number' => 'nullable|string|max:50',
            'status' => 'nullable|in:pending,sent,failed',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $query = Alert::with(['customer', 'order', 'user'])->latest();

        if (!empty($validated['lot_number'])) {
            $query->where('lot_number', $validated['lot_number']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $alerts = $query->paginate($validated['per_page'] ?? 20);

        return response()->json($alerts);
    }
}

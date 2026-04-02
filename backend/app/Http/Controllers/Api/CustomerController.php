<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    use ApiResponse;

    public function show(int $id): JsonResponse
    {
        $customer = Customer::with([
            'orders' => fn($q) => $q->latest('purchase_date')->limit(50),
            'orders.items.medication',
            'alerts' => fn($q) => $q->latest()->limit(20),
            'alerts.user',
        ])->findOrFail($id);

        return $this->success($customer);
    }
}

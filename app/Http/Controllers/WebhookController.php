<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WebhookController extends Controller
{
    protected $orderService;
    
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Retrieve the order data from the request
        $orderData = $request->toArray();
        // Process the order using the OrderService
        $this->orderService->processOrder($orderData);
        // Return a JSON response
        return response()->json([
            'message' => 'Order added successfully.',
            'success' => true,
        ], 200);
    }


}

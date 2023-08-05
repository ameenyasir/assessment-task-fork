<?php

namespace App\Http\Controllers;

use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Order;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $fromDate = $request->input('from');
        $toDate = $request->input('to');

        // Calculate the count of orders within the date range
        $orderCount = Order::whereBetween('created_at', [$fromDate, $toDate])->count();

        // Calculate the sum of order subtotals within the date range
        $revenue = Order::whereBetween('created_at', [$fromDate, $toDate])->sum('subtotal');

        // Calculate the sum of unpaid commissions for orders with an affiliate within the date range
        $commissionOwed = Order::whereBetween('created_at', [$fromDate, $toDate])->whereNotNull('affiliate_id')->sum('commission_owed');
        
        return response()->json([
            'count' => $orderCount,
            'commissions_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    }
}

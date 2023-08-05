<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderService
{
    protected $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */

    // When examining the migration of the order table, the absence of an order_id field indicates that the order_id is not a present attribute within the order table. This situation could arise due to divergent naming conventions or structural differences in the order table.

    // Pertaining to the merchant association, should the creation of a new affiliate lack the inclusion of a merchant ID in the data, it suggests that the affiliate may not possess a direct linkage with a specific merchant. It is plausible that the affiliate functions autonomously or maintains an alternative form of relationship with the merchants.

    // If your intention is to establish a connection between affiliates and merchants, you might contemplate generating a distinct table or association to track this relationship. Implementation could involve defining foreign keys or establishing a many-to-many relationship, contingent on your precise requisites.

    // Should you require further guidance regarding the merchant association or any other facet of your application, kindly provide additional details or furnish the extant structure of your tables. This will enable us to offer more targeted assistance.

    public function processOrder(array $data)
    {
        // TODO: Complete this method
        try {
            // Validate the request data
            $validator = Validator::make($data, [
                'order_id' => 'required|string',
                'subtotal_price' => 'required|numeric',
                'merchant_domain' => 'required|string',
                'discount_code' => 'required|string',
                'customer_name' => 'required|string',
                'customer_email' => 'required|email|unique:users',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Please verify as there seems to be an error in the data.',
                ], 500);
            }
            
            $merchant = Merchant::where(['domain' => $data['merchant_domain']])->first();
            
            $affiliate = Affiliate::with(['user' => function($query) use ($data){
                $query->where(['email' => $data['customer_email']]);
            }])->first();
            
            $order = new Order();
            // $order->external_order_id = $data['order_id'];
            $order->merchant_id = $merchant->id;
            $order->affiliate_id = $affiliate->id;
            $order->subtotal = $data['subtotal_price'];
            $order->commission_owed = ($data['subtotal_price']*$affiliate->commission_rate);
            $order->discount_code = $data['discount_code'];
            $order->payout_status = Order::STATUS_PAID;
            $order->save();
            return back();         
        } catch (\Exception $e) {
            // Handle the exception
            // dd($e->getMessage());
            return response()->json([
                'message' => 'Affiliate registration completed successfully.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

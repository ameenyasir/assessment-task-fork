<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
        try {
            // Validate the request data
            $validator = Validator::make($data, [
                'domain' => 'required|string',
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'api_key' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Please verify as there seems to be an error in the data.',
                ], 500);
            }

            // Register a new user
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['api_key']; // Remember to hash the password
            $user->type = User::TYPE_MERCHANT; // Set the user type according to your constants
            
            if($user->save()){
                // Register a new merchant
                $merchant = new Merchant();
                $merchant->user_id = $user->id;
                $merchant->display_name = $data['name'];
                $merchant->domain = $data['domain'];
                $merchant->turn_customers_into_affiliates = 1; // Set the merchant customers into affiliates'
                $merchant->default_commission_rate = 0.1; // Set the merchant default commission rate if you want then change it here
                $merchant->save();
                    
                return $merchant;

            }

        } catch (\Exception $e) {
            // Handle the exception
            // dd($e->getMessage());
            return response()->json([
                'message' => 'Registration attempts for both user and merchant have resulted in failures.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        try {
            // Validate the request data
            $validator = Validator::make($data, [
                'domain' => 'required|string',
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'api_key' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Please verify as there seems to be an error in the data.',
                ], 500);
            }

            $userId = $user->id;

            // Update User
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'message' => 'The user you are looking for could not be found.',
                ], 500);
            } 

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['api_key']); // Remember to hash the password
            $user->type = User::TYPE_MERCHANT; // Set the user type according to your constants

            if($user->save()){
                // Update Merchant
                $merchant = Merchant::where(['user_id' => $userId])->first();

                // You can also utilize this one to find a merchant. 
                // $merchant = Merchant::whereHas('users', function ($query) use ($userId) {
                //     $query->where('id', $userId);
                // })->first();

                if (!$merchant) {
                    return response()->json([
                        'message' => 'The merchant you are looking for could not be found.',
                    ], 500);
                } 

                $merchant->display_name = $data['name'];
                $merchant->domain = $data['domain'];
                $merchant->turn_customers_into_affiliates = 1; // Set the merchant customers into affiliates'
                $merchant->default_commission_rate = 0.1; // Set the merchant default commission rate if you want then change it here
                $merchant->save();

                // Offer a response or implement a redirection as the situation requires.
                return $merchant;

            }

        } catch (\Exception $e) {
            // Handle the exception
            // dd($e->getMessage());
            return response()->json([
                'message' => 'Updation attempts for both user and merchant have resulted in failures.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method
        try {
            // Find merchant by email
            $merchant = Merchant::with(['user' => function($query) use ($email){
                $query->where(['email' => $email]);
            }])->first();
            return $merchant;

        } catch (\Exception $e) {
            // Handle the exception
            // dd($e->getMessage());
            return response()->json([
                'message' => 'The merchant you are looking for could not be found.',
                'error' => $e->getMessage(),
            ], 500);
        }   
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
        $orders = Order::where(['affiliate_id' => $affiliate->id])->get();
        foreach ($orders as $order) {
            if($order->payout_status == Order::STATUS_UNPAID) {
                PayoutOrderJob::dispatch($order);
            }
        }

    }
}

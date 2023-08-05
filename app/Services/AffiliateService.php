<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AffiliateService
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method  
        $user = new User();

        $data = [
            'name' => $name,
            'email' => $email,
            'commissionRate' => $commissionRate
        ];

        // Validate the request data
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|max:255|unique:users',
            'commissionRate' => 'required|numeric'
        ]);

        if ($validator->fails() == true) {
            throw new AffiliateCreateException('Please verify as there seems to be an error in the data.');
        }
        
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make('password'); // Remember to hash the password
        $user->type = User::TYPE_AFFILIATE; // Set the user type according to your constants

        if($user->save()){
            $affiliate = new Affiliate();
            $affiliate->user_id = $user->id;
            $affiliate->merchant_id = $merchant->id;
            $affiliate->commission_rate = $commissionRate;
            $affiliate->discount_code = $this->apiService->createDiscountCode($merchant)['code']; // Set the merchant default commission rate if you want then change it here
            $affiliate->save();
            Mail::to($email)->send(new AffiliateCreated($affiliate));
            
            return $affiliate;

        }
                
    }
}

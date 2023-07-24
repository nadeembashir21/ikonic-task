<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

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
        $discountCode = $this->apiService->createDiscountCode($merchant);
        $user = User::firstOrCreate(
            [
                'email'     => $email,
            ],
            [
                'name'      => $name,
                'type'      => User::TYPE_AFFILIATE,
            ]);

        $affiliate = Affiliate::updateOrCreate(
            [
                'user_id'           => $user->id
            ],
            [
                'merchant_id'       => $merchant->id,
                'commission_rate'   => $commissionRate,
                'discount_code'     => is_string($discountCode['code'])?$discountCode['code'] : $discountCode['code']->serialize()
            ]);
        return $affiliate;
        
    }
}

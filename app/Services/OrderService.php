<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $o=Order::where($data['order_id'])->get();
        if(!$o->isEmpty()){
            return;
        }
        $user = User::updateOrCreate(
        [
            'email'     => $data['customer_email'],
        ],
        [
            'name'      => $data['customer_name'],
            'type'      => User::TYPE_AFFILIATE,
        ]);

        $merchant = Merchant::where('domain',$data['merchant_domain'])->first();                    
        if(!empty($merchant)){
            $affiliate = Affiliate::updateOrCreate(
            [
                'user_id'           => $user->id,
                'merchant_id'       => $merchant->id
            ],
            [                    
                'commission_rate'   => $merchant->default_commission_rate,
                'discount_code'     => $data['discount_code']
            ]);

            $order = Order::updateOrCreate(
            [
                'id'                => $data['order_id'],
                'merchant_id'       => $merchant->id
            ],
            [
                'subtotal'          => $data['subtotal_price'],
                'affiliate_id'      => $affiliate->id,                    
                'commission_owed'   => $data['subtotal_price'] * $affiliate->commission_rate,
                'external_order_id' => $data['order_id'],
                'external_order_id' => $data['order_id'] 
            ]);  
            $this->affiliateService->register($merchant,$data['customer_email'],$data['customer_name'],$affiliate->commission_rate);
        }  
        
    }
}

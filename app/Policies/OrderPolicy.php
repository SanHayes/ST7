<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Users;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // 是否本人订单
    public function own(Users $user, Order $order)
    {
        return $user->id === $order->user_id;
    }

    // 是否本人卖家订单
    public function saler(Users $user, Order $order)
    {
        return $user->id === $order->saler_id;
    }

    // 是否店铺所属订单
    public function shop(Users $user, Order $order)
    {
        return $user->id === $order->shop_id;
    }
}

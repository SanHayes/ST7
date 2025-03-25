<?php

namespace App\Policies;

use App\Models\Users;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserWalletLogPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * The Permission key the Policy corresponds to.
     *
     * @var string
     */
    public static $key = 'user_wallet_logs';

}

<?php

/**
 * Created by PhpStorm.
 * Users: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UsersWalletWithdraw extends Model
{
    protected $table = 'users_wallet_withdraw';
    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

}

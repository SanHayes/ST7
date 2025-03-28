<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCashInfoInternational extends Model
{
    protected $table = 'user_cash_info_international';
    public $timestamps = false;
    protected $appends = ['account_number'];



    public function getAccountNumberAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }

    /*
    public function setWechatNicknameAttribute($value)
    {
        $this->attributes['wechat_nickname'] = base64_encode($value);
    }
    */

    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }
}

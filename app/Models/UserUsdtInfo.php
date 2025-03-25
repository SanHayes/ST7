<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserUsdtInfo extends Model
{
    protected $table = 'user_usdt_info';
    public $timestamps = false;
    protected $appends = [];

    public function digitalCurrency()
    {
        return $this->belongsTo(DigitalCurrencySet::class, 'digital_currency_id', 'id');
    }



    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }
}

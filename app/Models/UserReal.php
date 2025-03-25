<?php
/**
 * Created by PhpStorm.
 * Users: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
class UserReal extends Model
{
    protected $table = 'user_real';
    public $timestamps = false;
    protected $hidden = [];
    protected $appends = ['account'];

    protected $casts = [
        'create_time' => 'datetime',
    ];

    public function getCreateTimeAttribute()
    {
        return  $this->attributes['create_time'];

    }
    public function getAccountAttribute()
    {

        $res=$this->belongsTo(Users::class, 'user_id', 'id')->value('phone');
        if(empty($res)){
             $res=$this->belongsTo(Users::class,  'user_id', 'id')->value('email');
        }
        return $res;

    }
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

}

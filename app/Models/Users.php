<?php

namespace App\Models;

use App\Models\AccountLog;
use App\Models\Agent;
use App\Models\Algebra;
use App\Models\MicroOrder;
use App\Models\Seller;
use App\Models\Token;
use App\Traits\dateTrait;
use App\Models\UserReal;
use App\Models\UserProfile;
use App\Models\UserAlgebra;
use App\Models\UsersWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Actionable;
use Mrlaozhou\Extend\Unlimitedable;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Overtrue\EasySms\PhoneNumber;

class Users extends Authenticatable implements Wallet, WalletFloat
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;
    use dateTrait;
    use Unlimitedable;
    use HasWallet, HasWallets;
    use HasWalletFloat;
    use Actionable;

    const TOKEN_DEFAULT = '';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * 缓存key
     * @return string
     */
    protected static function unlimitedCacheKey(): string
    {
        return 'users.parent';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'pay_password',
        'memorizing_words',
        'is_blacklist',
        'gesture_password',
        'risk',
        'remember_token',
    ];

    protected $appends = [
        'account',
        'is_seller',
        'create_date',
        'usdt',
        'caution_money',
        'parent_name',
        'my_agent_level',
        'userreal_name',
        'usdt_mic',
        'superior',
        'level',
        'level_fee'
    ];


    protected static $roleList = [
        MicroOrder::RESULT_LOSS => '亏损',
        MicroOrder::RESULT_BALANCE => '无',
        MicroOrder::RESULT_PROFIT => '盈利',
    ];

    public function getUserrealNameAttribute()
    {
        $user_profile = $this->userReal()->first();
        if ($user_profile) {
            return $user_profile->name ?? '--';
        } else {
            return '--';
        }

    }
    
    // public function mailMessages()
    // {
    //     return $this->hasMany(MailMessage::class, 'user_id', 'id');
    // }
        
    public function userReal()
    {
        return $this->hasMany(UserReal::class, 'user_id')->where('review_status', 2);
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_time' => 'datetime',
//        'password' => 'hashed',
    ];

    // 用户推荐下级列表
    public function sons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Users::class, 'parent_id', 'id');
    }

    /**
     * 获取登录用户手机号码
     * @param $notification
     * @return PhoneNumber
     */
    public function routeNotificationForEasySms($notification): PhoneNumber
    {
        return new PhoneNumber($this->mobile);
    }

    /**
     * Passport 登录支持 邮箱 和 手机号码
     * @param $username
     * @return mixed
     */
    public function findForPassport($username): mixed
    {
        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['mobile'] = $username;

        return self::where($credentials)->first();
    }

    //获取上级
    public function getSuperiorAttribute()
    {
        return self::with([])->where('id', $this->parent_id)->first()->email??'无';
    }

    public function getLeverBalanceAttribute()
    {
        $id = $this->getAttribute['id'];
        if(empty($id)){
            return '';
        }
        $wallet = UsersWallet::where('user_id', $id)->where('currency', 3)->first();
        return $wallet->lever_balance;
    }

    //会员等级
    public function getLevelAttribute()
    {
        $id = $this->getAttribute('user_level');
        if(empty($id)){
            return '普通会员';
        }
        $wallet = UserLevelModel::where('id', $id)->first();
        if($wallet){
            $name=$wallet->name;
        }else{
            $name='普通会员';
        }
        return $name;
    }
    //会员等级手续费
    public function getLevelFeeAttribute()
    {
        $id = $this->getAttribute('user_level');
        if(empty($id)){
            return 1;
        }
        $wallet = UserLevelModel::where('id', $id)->first();
        if($wallet){
            $fee=$wallet->give/100;
        }else{
            $fee=1;
        }
        return $fee;
    }
    public function getLockLeverBalanceAttribute()
    {
        $id = $this->getAttribute['id'];
        if(empty($id)){
            return '';
        }
        $wallet = UsersWallet::where('user_id', $id)->where('currency', 3)->first();
        return $wallet->lock_lever_balance;
    }
    public function getLegalBalanceAttribute()
    {
        $id = $this->getAttribute('id');
        if(empty($id)){
            return '';
        }
        $wallet = UsersWallet::where('user_id', $id)->where('currency', 3)->first();
        return $wallet->legal_balance;
    }
    public function getLockLegalBalanceAttribute()
    {
        $id = $this->getAttribute('id');
        $wallet = UsersWallet::where('user_id', $id)->where('currency', 3)->first();
        return $wallet->lock_legal_balance;
    }
    public function getUsdtMicAttribute()
    {
        $value = $this->getAttribute('id');
        if(empty($value)){
            return '';
        }
        $us = DB::table('currency')->where('name', 'USDT')->first();

        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $value)->first();

        return isset($wal->micro_balance) ? $wal->micro_balance : '0.00000';
    }
    //秒合约账号
    public function getUsdtAttribute()
    {
        $value = $this->getAttributes('id');
        if(empty($value)){
            return '';
        }
        $us = DB::table('currency')->where('name', 'USDT')->first();

        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $value)->first();
        $lever_balance = isset($wal->lever_balance) ? $wal->lever_balance : '0.00000';
        $change_balance = isset($wal->change_balance) ? $wal->change_balance : '0.00000';
        $micro_balance = isset($wal->micro_balance) ? $wal->micro_balance : '0.00000';

        return  $lever_balance + $change_balance + $micro_balance;
    }

    public function getCautionMoneyAttribute()
    {
        $value = $this->getAttributes('id');
        if(empty($value)){
            return '';
        }
        return DB::table('lever_transaction')->where('user_id', $value)->whereIn('status', [0, 1])->sum('caution_money');
    }

    public function getParentNameAttribute()
    {
        $value = $this->getAttribute('agent_note_id');
        $p = Agent::where('id', $value)->first();
        return isset($p->username) ? $p->username : '-/-';
    }

    public function getMyAgentLevelAttribute()
    {
        $value = $this->attributes['agent_id'] ?? 0;
        if ($value == 0) {
            return '普通用户';
        } else {
            $m = DB::table('agent')->where('id', $value)->first();
            $name = '';
            if (empty($m)) {
                $name = '';
            } else {
                if ($m->level == 0) {
                    $name = '超管';
                } else if ($m->level > 0) {
                    $name = $m->level . '级代理商';
                }
            }

            return $name;
        }
    }

    public function getCreateDateAttribute()
    {
        $value = $this->getAttribute('time');
        return $value;
        return date('Y-m-d H:i:s', $value);
    }

    //密码加密
    public static function MakePassword($password, $type = 0)
    {

        if ($type == 0) {
            $salt = 'ABCDEFG';
            $passwordChars = str_split($password);
            foreach ($passwordChars as $char) {
                $salt .= md5($char);
            }
        } else {
            $salt = 'TPSHOP' . $password;
        }
        return md5($salt);
    }

    public static function getByAccountNumber($account_number)
    {
        return self::where('account_number', $account_number)->first();
    }

    public static function getByString($string)
    {
        if (empty($string)) {
            return "";
        }
        return self::where("phone", $string)
            ->orwhere('email', $string)
            ->orWhere('account_number', $string)
            ->first();
    }

    public static function getById($id)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->first();
    }
    
    /**
     * 模型的启动方法
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // 在创建模型时自动生成邀请码
        static::creating(function ($model) {
            $model->extension_code = self::getExtensionCode();
            $model->account_number = $model->email;
        });
    }
    //生成邀请码
    public static function getExtensionCode()
    {
        $code = self::generate_password(4);
        if (self::where("extension_code", $code)->first()) {
            //如果生成的邀请码存在，继续生成，直到不存在
            $code = self::getExtensionCode();
        }
        return $code;
    }
    public static function generate_password($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $password = "";
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    public static function getUserId()
    {
        // return session('user_id');
        $token = Token::getToken();
        $user_id = Token::getUserIdByToken($token);
        return $user_id;
    }

    public static function getAuthUser()
    {
        return self::find(self::getUserId());
    }


    public function getTimeAttribute()
    {
        if (isset($this->attributes['time'])) {
            $value = $this->attributes['time'];
            return $value ? date('Y-m-d H:i:s', $value) : '';
        } else {
            return "";
        }
    }

    //获取用户的账号  手机号或邮箱
    public function getAccountAttribute()
    {
        //$value = $this->attributes['phone'];
        $value = $this->getAttribute('phone');
        if (empty($value)) {
            $value = $this->getAttribute('email');
            if(empty($value)){
                return '';
            }
            $n = strripos($value, '@');
            $value = mb_substr($value, 0, 2) . '******' . mb_substr($value, $n);
        } else {
            $value = mb_substr($value, 0, 3) . '******' . mb_substr($value, -3, 3);
        }
        return $value;
    }
       public function setPasswordAttribute($value) {
        $this->attributes['password'] = $this->MakePassword($value);
    }
    /*
    //手势密码序列化
    public function setGesturePassword($value) {
        $this->attributes['gesture_password'] = serialize($value);
    }

    //取出数据时反序列化
    public function getGesturePassword($value) {
        return unserialize($value);
    }
    */
    

    public function getIsSellerAttribute()
    {
        $id=$this->getAttribute('id');
        if(empty($id)){
            return 0;
        }
        $seller = Seller::where('user_id', $this->getAttribute('id'))->first();
        if (!empty($seller)) {
            return 1;
        }
        return 0;
    }

    public function cashinfo()
    {
        return $this->belongsTo(UserCashInfo::class, 'id', 'user_id');
    }

    public function legalDeal()
    {
        return $this->hasOne(C2cDeal::class, 'seller_id', 'id');
    }



    /*
     * count 当前几代
     * $algebra 总共几代
     * user_id 用户id
     * touch_user_id 触发者id
     * currency 币种id
     * price 金额
     * */
    public static function rebate($user_id,$touch_user_id,$currency,$price,$count=1,$algebra=0)
    {
        $user=self::where('id', $user_id)->first();
        $touch_user = self::getById($touch_user_id);
        if (empty($user)) {
            return true;
        }

        if ($user->parent_id==0) {
            return true;
        }
        $wallet = UsersWallet::where('currency', $currency)
            ->where('user_id', $user->parent_id)
            ->first();

        $u_algebra=Algebra::where('algebra', $count)->first();
        if (empty($u_algebra)||empty($wallet)) {
            $count+=1;
            $algebra-=1;
            $result=self::rebate($user->parent_id, $touch_user_id, $currency, $price, $count, $algebra);
            return $result;
        }

        $totle_price=$price*$u_algebra->rate/100;
        $info='第'.$count."代用户{$touch_user->account_number}返手续费：".$totle_price;
        $result = change_wallet_balance($wallet, 4, $totle_price, AccountLog::MICRO_TRADE_CLOSE_SETTLE, $info);
        $algebra-=1;
        $user_algebra=new UserAlgebra();
        $user_algebra->user_id=$user->parent_id;
        $user_algebra->touch_user_id=$touch_user_id;
        $user_algebra->algebra=$count;
        $user_algebra->info=$info;
        $user_algebra->value=$totle_price;
        $user_algebra->save();
        $count+=1;
        if ($algebra==0) {
            return true;
        }else{
            $result=self::rebate($user->parent_id, $touch_user_id, $currency, $price, $count, $algebra);
            return $result;
        }

    }

    public function belongAgent()
    {
        return $this->belongsTo(Agent::class, 'agent_note_id', 'id');
    }
}

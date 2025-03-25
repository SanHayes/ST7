<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Agent;
use App\Models\UserCashInfo;
use App\Models\UserChat;
use App\Models\UserReal;
use App\Models\Users;
use App\Models\Token;
use App\Models\AccountLog;
use App\Models\UsersWallet;
use App\Models\Currency;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\DAO\RewardDAO;
use App\Models\UserProfile;
use App\Models\LhBankAccount;
use App\Models\Setting;


class LoginController extends Controller
{

    /**
     * 生成验证码
     * @return void
     */
    public function verification(){
       return app('captcha')->create('default', true);
    }

    //用户登陆
    public function login(Request $request)
    {
        $user_string = $request->input('user_string', '');
        $password = $request->input('password', '');
        $type = $request->input('type', 1);
        $captcha = $request->input('captcha'); //验证码
        $key = $request->input('key'); //验证码

//        if (!captcha_api_check($captcha, $key)){
//            return $this->error('验证码有误');
//        }
        if (empty($user_string)) {
            return $this->error('请输入账号');
        }
        if (empty($password)) {
            return $this->error('请输入密码');
        }
        // 手机、邮箱、交易账号登录
        $user = Users::where('phone', $user_string)->orWhere('email', $user_string)->first();
        if (empty($user)) {
            return $this->error('用户未找到');
        }
        if ($type == 1) {
               
            if (Users::MakePassword($password) != $user->password) {
                return $this->error('密码错误');
            }
        }
        if ($type == 2) {
            if ($password != $user->gesture_password) {
                return $this->error('手势密码错误');
            }
        }
         
        // 是否锁定
        if ($user->status == 1) {
            return $this->error('您好，您的账户已被锁定，详情请咨询客服。');
        }

        $token = $user->createToken('Token Name')->accessToken;
        
        if($user->simulation!=1){
            UsersWallet::makeWallet($user->id);
        }else{
            UsersWallet::makeWalletSimulation($user->id);
        }
        
        return $this->success("登录成功",0,$token);
    }

    // 注册 add 邮箱注册
    public function register(Request $request)
    {
        $area_code_id = $request->get('area_code_id', 0); // 注册区号
        $area_code = $request->get('area_code', 0); // 注册区号
        $type = $request->get('type', '');
        $user_string = $request->get('user_string', null);
        $password = $request->get('password', '');
        $re_password = $request->get('re_password', '');
        $code = $request->get('code', '');
        $extension_code=$request->get('extension_code', '');
        // $passwo1rd1 =Users::MakePassword($password);
        //  if ($password !=66666) {
            
        //     return $this->error( Users::MakePassword($passwo1rd1));
        // }
        if (empty($type) || empty($user_string) || empty($password) || empty($re_password)) {
            return $this->error('参数错误');
        }
        if ($password != $re_password) {
            return $this->error('两次密码不一致');
        }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
        $key = 'verificationCode_' . $user_string;

        $verifyData = Cache::get($key);
        if (!$verifyData) {
            $data['message'] = "验证码已失效！";
            return response()->json($data, 403);
        }
        if ($code != $verifyData['code'] && $type == "email") {
            return $this->error('验证码错误');
        }
        $user = Users::getByString($user_string);
        if (!empty($user)) {
            return $this->error('账号已存在');
        }
        $parent_id = 0;
        if (!empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error("请填写正确的邀请码");
            } else {
                $parent_id = $p->id;
            }
        }

        $users = new Users();
        $users->password = $password;
        $users->parent_id = $parent_id;
        $users->account_number = $user_string;
        $users->area_code_id = $area_code_id;
        $users->area_code = $area_code;
        if ($type == "mobile") {
            $users->phone = empty($user_string) ? null : $user_string;
        } else {
            $users->email = $user_string;
            $users->phone = null;
        }

        // 后台设置用户默认头像
        $user_default_avatar = DB::table('settings')->where('key', 'user_default_avatar')->first();

        $users->head_portrait = URL($user_default_avatar->value);
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->parents_path = UserDAO::getRealParentsPath($users); // 生成parents_path tian add

            // 代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
            // 代理商节点关系
            $users->agent_path = Agent::agentPath($parent_id);

            $users->save(); // 保存到user表中
            UsersWallet::makeWallet($users->id);
            // DB::rollBack();
            //创建bank账号
            LhBankAccount::newAccount($users->id, $parent_id);
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });

            DB::commit();
            Cache::forget($key);
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }


    // 注册 add 手机注册
    public function phoneregister()
    {

        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        $area_code = Input::get('area_code', 0); // 注册区号
        $type = Input::get('type', '');
        $user_string = Input::get('user_string', null);
        $password = Input::get('password', '');
        $re_password = Input::get('re_password', '');
        $code = Input::get('code', '');
        if (empty($type) || empty($user_string) || empty($password) || empty($re_password)) {
            return $this->error('参数错误');
        }
        $extension_code = Input::get('extension_code', '');
        if ($password != $re_password) {
            return $this->error('两次密码不一致');
        }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
        if ($code != session('code') && $type == "email") {
            return $this->error('验证码错误');
        }
        $user = Users::getByString($user_string);
        if (!empty($user)) {
            return $this->error('账号已存在');
        }
        $parent_id = 0;


        // 2021-09-09  修改为 根据后台开关  验证邀请码是否必填
        $sharar_radio = DB::table('settings')->where('key', 'sharar_radio')->first();
        // dump($sharar_radio);die;
        if ($sharar_radio->value == 1 && empty($extension_code)) {

            return $this->error("请填写正确的邀请码");
        }
        // 修改结束

        if (!empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error("请填写正确的邀请码");
            } else {
                $parent_id = $p->id;
            }
        }
        $users = new Users();
        $users->password = $password;
        $users->parent_id = $parent_id;
        $users->account_number = $user_string;
        $users->area_code_id = $area_code_id;
        $users->area_code = $area_code;
        if ($type == "mobile") {
            $users->phone = empty($user_string) ? null : $user_string;
        } else {
            $users->email = $user_string;
            $users->phone = null;
        }

        // 后台设置用户默认头像
        $user_default_avatar = DB::table('settings')->where('key', 'user_default_avatar')->first();

        $users->head_portrait = URL($user_default_avatar->value);
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->parents_path = UserDAO::getRealParentsPath($users); // 生成parents_path tian add

            // 代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
            // 代理商节点关系
            $users->agent_path = Agent::agentPath($parent_id);

            $users->save(); // 保存到user表中
            $test = UsersWallet::makeWallet($users->id);
            // DB::rollBack();
            //创建bank账号
            LhBankAccount::newAccount($users->id, $parent_id);
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });


            // $userreal = new UserReal();

            // $userreal->user_id = $users->id;
            // $userreal->name = "杨根思";
            // $userreal->card_id = "371311199508071145";
            // $userreal->create_time = time();
            // $userreal->review_status = 2;

            // $userreal->save();


            DB::commit();
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }


    // 修改密码
    public function updatePassword(Request $request)
    {
        $user= $request->user();

        $password = $request->get('password', '');

        $repassword = $request->get('repassword', '');

        $code = $request->get('code', '');

        $key = 'verificationCode_' . $user->email;

        $verifyData = Cache::get($key);
        if (!$verifyData) {
            $data['message'] = "验证码已失效！";
            return response()->json($data, 403);
        }
        if ($code != $verifyData['code']) {
            return $this->error('验证码错误');
        }
        if (empty($password) || empty($repassword)) {
            return $this->error('请输入密码或确认密码');
        }

        if ($repassword != $password) {
            return $this->error('输入两次密码不一致');
        }

        $user = Users::getByString($user->email);
        if (empty($user)) {
            return $this->error('账号不存在');
        }

        $user->password = $password;
        try {
            $user->save();
            Cache::forget($key);
            return $this->success("修改密码成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }


    // 忘记密码
    public function forgetPassword(Request $request)
    {
        $email= $request->get('email', '');

        $password = $request->get('password', '');

        $repassword = $request->get('repassword', '');

        $code = $request->get('code', '');

        $user = Users::getByString($email);
        if (empty($user)) {
            return $this->error('账号不存在');
        }

        $key = 'verificationCode_' . $email;

        $verifyData = Cache::get($key);
        if (!$verifyData) {
            $data['message'] = "验证码已失效！";
            return response()->json($data, 403);
        }
        if ($code != $verifyData['code']) {
            return $this->error('验证码错误');
        }
        if (empty($password) || empty($repassword)) {
            return $this->error('请输入密码或确认密码');
        }

        if ($repassword != $password) {
            return $this->error('输入两次密码不一致');
        }

        $user->password = $password;
        try {
            $user->save();
            Cache::forget($key);
            return $this->success("修改密码成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function checkEmailCode()
    {
        $email_code = Input::get('email_code', '');
        if (empty($email_code))
            return $this->error('请输入验证码');
        $session_code = session('code');
        // dump($email_code.'__code');
        // dump($session_code);die;
        if ($email_code != $session_code)
            return $this->error('验证码错误');
        return $this->success('验证成功');
    }

    public function checkMobileCode()
    {
        $mobile_code = Input::get('mobile_code', '');
        // var_dump($mobile_code);
        // if (empty($mobile_code)) {
        //     return $this->error('请输入验证码');
        // }
        $session_mobile = session('code');
        // var_dump($session_mobile);
        // if ($session_mobile != $mobile_code && $mobile_code != '9188') {
        //     return $this->error('验证码错误');
        // }
        return $this->success('验证成功');
    }

    //后台登录
    public function adminLogin(Request $request)
    {

        $username = $request->input('username', '');
        $password = $request->input('password', '');
//        $captcha = $request->input('captcha'); //验证码
//        $key = $request->input('key'); //验证码
//
//        if (!captcha_api_check($captcha, $key)){
//            return $this->error('验证码有误');
//        }
        if (empty($username)) {
            return $this->error('请输入账号');
        }
        if (empty($password)) {
            return $this->error('请输入密码');
        }
        $password = Users::MakePassword($password);
        $admin = Admin::where('username', $username)->where('password', $password)->first();
        if (empty($admin)) {
            return $this->error('用户名密码错误');
        } else {
            $token = $admin->createToken('Token Name')->accessToken;
            return $this->success("登录成功",0,['token'=>$token,'user'=>$admin]);
        }

    }
}


## 关于

基于 Laravel 10 和 Nova 后台框架


## 安装和运行

```bash
cd ~/Code/
git clone git@github.com:imnpc/base10nova.git project
```

复制 Nova授权文件 auth.json 文件到 项目根目录下,

修改 .env 文件,配置数据库信息,

导入数据库文件 base10nova.sql,

然后继续执行

```bash
composer install

php artisan key:generate

php artisan storage:link

php artisan migrate
```

## 管理后台

```bash
/admin
帐号:admin@admin.com
密码:admin2023888
```

## 说明

### 所有后台控制器都位于 /app/Nova 目录下
/app/Nova/AdminUser.php  后台管理员

/app/Nova/User.php  用户

/app/Nova/Settings/General.php 常规设置

/app/Nova/Settings/Site.php 站点设置

设置项前台调用

nova_get_setting('link'); // link 是要调用的字段

操作用户钱包
$logService = app()->make(LogService::class); // 钱包服务初始化
$remark = "奖励金额 " . $money . ' ,订单号 #' . $this->order->order_sn;
$logService->userWalletLog($user->id, 1, $money, 0, $day, FromType::ORDER, $remark, $this->order->id);


## 常用命令

```bash

// 创建模型和数据迁移文件
php artisan make:model Order -m
// 创建 API 控制器
php artisan make:controller Api/OrderController --model=App\\Models\\Order
// 创建 资源
php artisan make:resource OrderResource
// 创建 策略授权
php artisan make:policy OrderPolicy

// 生成后台资源
php artisan nova:resource Order

// 创建 枚举类 (第三方)
php artisan make:enum OrderStatus
```

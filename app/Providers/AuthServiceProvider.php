<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Route;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // token 认证有效期 365 天
        Passport::tokensExpireIn(now()->addDays(365));
        // 刷新 token 认证有效期 400 天
        Passport::refreshTokensExpireIn(now()->addDays(400));
        // 设置令牌过期时间 1 年
        Passport::personalAccessTokensExpireIn(now()->addMonths(12));

        Gate::guessPolicyNamesUsing(function ($modelClass) {
            return 'App\Policies\\' . class_basename($modelClass) . 'Policy'; // 动态返回模型对应的策略名称，如：// 'App\Models\Users' => 'App\Policies\UserPolicy',
        });
    }
}

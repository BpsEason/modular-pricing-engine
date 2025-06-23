<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Price\PriceCalculator;
use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\Contracts\NotifierInterface;
use App\Domain\Price\Notifiers\EmailNotifier;
use App\Domain\Price\Notifiers\LoggerNotifier;

class PricingEngineServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PriceCalculator::class, function ($app) {
            $modifiers = collect(config('pricing.modifiers'))
                ->map(fn($modifierClass) => $app->make($modifierClass))
                ->filter(fn($modifier) => $modifier instanceof PriceModifierInterface)
                ->toArray();

            return new PriceCalculator($modifiers);
        });

        // 綁定 NotifierInterface 到一個由 LoggerNotifier 裝飾 EmailNotifier 的實例
        $this->app->singleton(NotifierInterface::class, function ($app) {
            $baseNotifierClass = config('pricing.notifier.base_notifier', EmailNotifier::class);
            $decoratedNotifierClass = config('pricing.notifier.decorated_notifier', LoggerNotifier::class);

            $baseNotifier = $app->make($baseNotifierClass);
            
            // 如果decoratedNotifierClass是NotifierDecorator的子類並且不是EmailNotifier本身
            if (is_subclass_of($decoratedNotifierClass, \App\Domain\Price\Notifiers\NotifierDecorator::class) && $decoratedNotifierClass !== EmailNotifier::class) {
                return $app->make($decoratedNotifierClass, ['notifier' => $baseNotifier]);
            }
            
            return $baseNotifier; // 如果沒有裝飾器配置，則直接返回基礎通知器
        });

        // 也可以選擇性地綁定具體的通知器類別，以便直接解析
        $this->app->bind(EmailNotifier::class, function ($app) {
            return new EmailNotifier();
        });

        $this->app->bind(LoggerNotifier::class, function ($app) {
            // LoggerNotifier 需要一個 NotifierInterface 實例作為構造函數參數
            // 這裡我們讓它裝飾 EmailNotifier
            return new LoggerNotifier($app->make(EmailNotifier::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 可以選擇發布配置，但在此腳本中我們直接生成了
        // $this->publishes([
        //     __DIR__.'/../../config/pricing.php' => config_path('pricing.php'),
        // ], 'pricing-config');
    }
}

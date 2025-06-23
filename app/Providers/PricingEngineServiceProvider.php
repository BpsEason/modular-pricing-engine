<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Price\PriceCalculator;
use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Services\OrderPricingService;
use App\Domain\Notifications\Contracts\Notifier; // 如果你實作通知部分
use App\Domain\Notifications\BaseNotifier; // 基礎通知器

class PricingEngineServiceProvider extends ServiceProvider
{
    /**
     * 註冊應用程式服務。
     */
    public function register(): void
    {
        // 合併你的配置檔 (如果你的專案將來會作為 Composer 套件發布)
        $this->mergeConfigFrom(
            __DIR__.'/../../config/pricing.php', 'pricing'
        );

        // 綁定 PriceCalculator 到 Laravel 服務容器 (責任鏈模式)
        // 當請求 PriceCalculator 時，容器會自動解析並傳入所有配置的 PriceModifier
        $this->app->singleton(PriceCalculator::class, function ($app) {
            $modifierClasses = config('pricing.modifiers', []);
            $modifiers = [];
            foreach ($modifierClasses as $modifierClass) {
                // 從容器解析每個 Modifier，允許 Modifier 自身有依賴
                $modifiers[] = $app->make($modifierClass);
            }
            return new PriceCalculator($modifiers);
        });

        // 綁定 OrderPricingService 到 Laravel 服務容器
        $this->app->singleton(OrderPricingService::class, function ($app) {
            return new OrderPricingService(
                $app->make(PriceCalculator::class) // 注入 PriceCalculator
            );
        });

        // --- 通知裝飾器模式綁定範例 (可選) ---
        // if (config('pricing.default_notifier')) {
        //     $this->app->singleton(Notifier::class, function ($app) {
        //         // 先解析基礎通知器
        //         $notifier = $app->make(config('pricing.default_notifier', BaseNotifier::class));
        //         // 接著依序應用所有配置的裝飾器
        //         foreach (config('pricing.notification_decorators', []) as $decoratorClass) {
        //             $notifier = $app->make($decoratorClass, ['notifier' => $notifier]);
        //         }
        //         return $notifier;
        //     });
        // }
    }

    /**
     * 啟動任何應用程式服務。
     */
    public function boot(): void
    {
        // 如果你希望 config/pricing.php 可以被使用者發佈到他們的 config 目錄
        // $this->publishes([
        //     __DIR__.'/../../config/pricing.php' => config_path('pricing.php'),
        // ], 'pricing-engine-config');
    }
}

<?php

use App\Domain\Price\Modifiers\MemberDiscountModifier;
use App\Domain\Price\Modifiers\FlashSaleModifier;
use App\Domain\Price\Modifiers\CouponModifier;
use App\Domain\Price\Modifiers\PaymentMethodModifier;

// 如果有實作通知裝飾器，則會用到這些
// use App\Domain\Notifications\Decorators\LoggerNotifierDecorator;
// use App\Domain\Notifications\Decorators\DelayNotifierDecorator; // 待實作

return [
    /*
    |--------------------------------------------------------------------------
    | Price Modifiers Configuration (策略模式與責任鏈)
    |--------------------------------------------------------------------------
    |
    | 定義價格修飾器應用的順序。順序至關重要，因為它會影響最終計算價格。
    | 每個類別都必須實作 App\Domain\Price\Contracts\PriceModifierInterface。
    | 優先級 (priority) 值越小，越先被應用。
    |
    */
    'modifiers' => [
        // 建議先應用基於整體或商品的折扣
        FlashSaleModifier::class,   // 閃購折扣 (高優先級，例如 priority 100)
        CouponModifier::class,      // 優惠券折扣 (次高優先級，例如 priority 150)

        // 然後是基於用戶屬性的折扣
        MemberDiscountModifier::class, // 會員分級折扣 (中等優先級，例如 priority 200)

        // 最後是基於支付方式的折抵
        PaymentMethodModifier::class, // 支付方式折扣 (低優先級，例如 priority 300)
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Decorators Configuration (裝飾器模式)
    |--------------------------------------------------------------------------
    |
    | 定義通知發送器應該如何被裝飾。
    | 順序定義了包裝的序列 (由內到外)。
    | 範例: BaseNotifier -> LoggerDecorator -> DelayDecorator
    |
    */
    'notification_decorators' => [
        // App\Domain\Notifications\Decorators\LoggerNotifierDecorator::class,
        // App\Domain\Notifications\Decorators\DelayNotifierDecorator::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Notifier Implementation (預設通知實作)
    |--------------------------------------------------------------------------
    |
    | 定義在應用任何裝飾器之前使用的基礎通知器。
    |
    */
    // 'default_notifier' => App\Domain\Notifications\BaseNotifier::class,
];

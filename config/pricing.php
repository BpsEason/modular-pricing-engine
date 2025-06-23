<?php

use App\Domain\Price\Modifiers\CouponModifier;
use App\Domain\Price\Modifiers\FlashSaleModifier;
use App\Domain\Price\Modifiers\MemberDiscountModifier;
use App\Domain\Price\Modifiers\PaymentMethodModifier;
use App\Domain\Price\Modifiers\HolidayDiscountModifier;
use App\Domain\Price\Notifiers\EmailNotifier;
use App\Domain\Price\Notifiers\LoggerNotifier;

return [
    'modifiers' => [
        FlashSaleModifier::class,
        HolidayDiscountModifier::class,
        CouponModifier::class,
        MemberDiscountModifier::class,
        PaymentMethodModifier::class,
    ],

    'notifier' => [
        // 'default' => 'email', // 可以定義默認使用的通知器別名
        'channels' => [
            'email' => EmailNotifier::class,
            'logger' => LoggerNotifier::class,
        ],
        // 如果需要使用裝飾器模式，設定 decorated_notifier 為裝飾器，base_notifier 為被裝飾者
        'decorated_notifier' => LoggerNotifier::class, // 例如：希望 LoggerNotifier 裝飾 EmailNotifier
        'base_notifier' => EmailNotifier::class,      // 被裝飾的基礎通知器
    ],
];

<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;

class PaymentMethodModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        if (!$context->paymentMethod) {
            return $context; // 沒有指定支付方式，不應用折扣
        }

        $discount = 0;
        // 根據支付方式設定折扣金額或比率 (可從資料庫或配置中取得)
        switch ($context->paymentMethod) {
            case 'credit_card':
                $discount = 0; // 信用卡無額外折扣
                break;
            case 'line_pay':
                // 假設 Line Pay 有 2% 折扣
                $discount = -$context->currentPrice * 0.02;
                break;
            case 'bank_transfer':
                // 假設銀行轉帳有固定金額折抵
                $discount = -10; // 固定折抵 10 元
                break;
        }

        if ($discount < 0) {
            $context->addModification('payment_method_discount', $discount);
        }

        return $context;
    }

    /**
     * 定義支付方式折扣的優先級。
     */
    public function getPriority(): int
    {
        return 300; // 低優先級，通常最後應用，因為是基於最終支付金額
    }
}

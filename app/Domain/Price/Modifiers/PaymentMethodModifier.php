<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;

class PaymentMethodModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        $paymentMethod = $context->getPaymentMethod();

        #region 這裡應該從資料庫獲取支付方式回饋，目前為硬編碼演示
        $paymentDiscounts = [
            'credit_card_visa' => 0.02, // Visa 信用卡 2% 回饋
            'line_pay' => 0.03, // Line Pay 3% 回饋
        ];
        #endregion

        if ($paymentMethod && isset($paymentDiscounts[$paymentMethod])) {
            $discount = -$context->currentPrice * $paymentDiscounts[$paymentMethod];
            $context->addModification('payment_method_discount', $discount);
        }

        return $context;
    }

    public function getPriority(): int
    {
        return 200; // 支付方式折扣通常在最後
    }
}

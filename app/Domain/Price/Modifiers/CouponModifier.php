<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;

class CouponModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        $couponCode = $context->getCouponCode();

        #region 這裡應該從資料庫獲取優惠券資訊，目前為硬編碼演示
        $coupons = [
            'SAVE20' => 20.00, // 固定減免
            'PERCENT10' => 0.10, // 百分比折扣
        ];
        #endregion

        if ($couponCode && isset($coupons[$couponCode])) {
            $discountAmount = 0;
            if ($couponCode === 'SAVE20') {
                $discountAmount = -$coupons[$couponCode];
            } elseif ($couponCode === 'PERCENT10') {
                $discountAmount = -$context->currentPrice * $coupons[$couponCode];
            }

            $context->addModification('coupon_discount', $discountAmount);
        }

        return $context;
    }

    public function getPriority(): int
    {
        return 150; // 在閃購後，會員折扣前或後
    }
}

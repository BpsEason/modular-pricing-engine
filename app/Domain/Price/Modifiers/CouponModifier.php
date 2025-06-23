<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;

class CouponModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        if (!$context->couponCode) {
            return $context; // 沒有優惠券代碼，不應用折扣
        }

        // 實際應用中，會根據 $context->couponCode 從資料庫查詢優惠券資訊
        $couponData = $this->getCouponDetails($context->couponCode);

        if ($couponData && $this->isCouponValid($couponData, $context)) {
            $discountAmount = 0;
            switch ($couponData['type']) {
                case 'percentage':
                    $discountAmount = -$context->currentPrice * ($couponData['value'] / 100);
                    break;
                case 'fixed_amount':
                    $discountAmount = -$couponData['value'];
                    break;
                // ... 其他優惠券類型
            }
            $context->addModification('coupon_discount', $discountAmount);
        }

        return $context;
    }

    /**
     * 模擬從資料庫查詢優惠券詳細資訊。
     */
    protected function getCouponDetails(string $couponCode): ?array
    {
        $coupons = [
            'SAVE20' => ['type' => 'fixed_amount', 'value' => 20, 'min_order' => 100], // 滿100減20
            'SAVE10PERCENT' => ['type' => 'percentage', 'value' => 10, 'min_order' => 50], // 滿50打9折
        ];
        return $coupons[strtoupper($couponCode)] ?? null; // 轉換為大寫以匹配
    }

    /**
     * 模擬檢查優惠券是否有效 (例如：最低消費金額)。
     */
    protected function isCouponValid(array $couponData, PriceContext $context): bool
    {
        // 檢查最低消費金額
        return $context->currentPrice >= ($couponData['min_order'] ?? 0);
        // 實際還需檢查有效期限、使用次數、適用商品等
    }

    /**
     * 定義優惠券折扣的優先級。
     */
    public function getPriority(): int
    {
        return 150; // 中高優先級
    }
}

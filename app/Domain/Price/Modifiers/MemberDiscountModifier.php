<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;

class MemberDiscountModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        $userId = $context->getUserId();

        #region 這裡應該從資料庫獲取會員等級或折扣，目前為硬編碼演示
        $memberDiscounts = [
            1 => 0.05, // 用戶 ID 1 享 5% 折扣
            2 => 0.10, // 用戶 ID 2 享 10% 折扣
        ];
        #endregion

        if ($userId && isset($memberDiscounts[$userId])) {
            $discount = -$context->currentPrice * $memberDiscounts[$userId];
            $context->addModification('member_discount', $discount);
        }

        return $context;
    }

    public function getPriority(): int
    {
        return 180; // 在大部分折扣之後，支付方式之前
    }
}

<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;

class MemberDiscountModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        // 如果沒有用戶或用戶沒有會員等級，則不應用折扣
        if (!$context->user || !isset($context->user->member_level)) {
            return $context;
        }

        $discountRate = 0;
        // 根據會員等級設定折扣率 (這部分邏輯可從資料庫或配置中取得)
        switch ($context->user->member_level) {
            case 'gold':
                $discountRate = 0.05; // 黃金會員 5% 折扣
                break;
            case 'platinum':
                $discountRate = 0.10; // 白金會員 10% 折扣
                break;
            // ... 更多會員等級
        }

        if ($discountRate > 0) {
            // 折扣金額為負數，表示減少價格
            $discountAmount = -$context->currentPrice * $discountRate;
            $context->addModification('member_discount', $discountAmount);
        }

        return $context;
    }

    /**
     * 定義會員折扣的優先級。
     * 數值越小，表示優先級越高，越早被應用。
     */
    public function getPriority(): int
    {
        return 200; // 中等優先級，通常在基礎促銷後應用
    }
}

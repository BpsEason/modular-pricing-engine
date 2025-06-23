<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;
use Carbon\Carbon;

class FlashSaleModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        #region 這裡應該從資料庫獲取閃購活動，目前為硬編碼演示
        $flashSaleActive = Carbon::now()->between(
            Carbon::parse('2025-01-01 00:00:00'),
            Carbon::parse('2025-01-01 23:59:59')
        );
        #endregion

        if ($flashSaleActive) {
            // 假設閃購給予商品價格的 20% 折扣
            // 這裡應根據實際商品和閃購規則計算
            $discount = -$context->currentPrice * 0.2; // 簡單示範
            
            $context->addModification('flash_sale', $discount);
        }

        return $context;
    }

    public function getPriority(): int
    {
        return 100; // 閃購通常優先級最高
    }
}

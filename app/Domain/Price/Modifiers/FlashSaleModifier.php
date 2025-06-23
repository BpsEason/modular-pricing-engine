<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;
use Carbon\Carbon; // Laravel 預設包含 Carbon

class FlashSaleModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        // 實際應用中，這裡會從資料庫或快取檢查當前是否有生效的閃購活動
        $isFlashSaleActive = $this->checkFlashSaleStatus();
        $hasFlashSaleItem = $this->hasFlashSaleItem($context->items);

        if ($isFlashSaleActive && $hasFlashSaleItem) {
            // 閃購折扣通常是針對商品，這裡簡化為對當前總價的折扣
            $discountAmount = -$context->currentPrice * 0.15; // 15% 閃購折扣
            $context->addModification('flash_sale', $discountAmount);
        }

        return $context;
    }

    /**
     * 檢查是否有生效中的閃購活動。
     * 實際應從資料庫查詢設定。
     */
    protected function checkFlashSaleStatus(): bool
    {
        $now = Carbon::now();
        // 這裡設定一個示範的閃購時間段
        // IMPORTANT: Update these dates before using this in a real project or for a public demo
        $startTime = Carbon::parse('2025-06-23 10:00:00');
        $endTime = Carbon::parse('2025-06-23 23:59:59');

        return $now->between($startTime, $endTime);
    }

    /**
     * 判斷訂單中是否包含閃購商品。
     * 實際應根據商品資料判斷。
     */
    protected function hasFlashSaleItem(array $items): bool
    {
        foreach ($items as $item) {
            // 假設商品資料中有 'is_flash_sale' 標記
            if (isset($item['is_flash_sale']) && $item['is_flash_sale']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 定義閃購折扣的優先級。
     */
    public function getPriority(): int
    {
        return 100; // 高優先級，通常優先應用，因為是限時優惠
    }
}

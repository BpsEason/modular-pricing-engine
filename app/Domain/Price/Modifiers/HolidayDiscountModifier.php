<?php

namespace App\Domain\Price\Modifiers;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;
use Carbon\Carbon;

class HolidayDiscountModifier implements PriceModifierInterface
{
    public function apply(PriceContext $context): PriceContext
    {
        // 假設聖誕節有折扣
        if (Carbon::now()->month === 12 && Carbon::now()->day === 25) {
            $discount = -$context->currentPrice * 0.10; // 10% 折扣
            $context->addModification('holiday_discount', $discount);
        }
        return $context;
    }

    public function getPriority(): int
    {
        return 120; // 介於閃購(100)與優惠券(150)之間
    }
}

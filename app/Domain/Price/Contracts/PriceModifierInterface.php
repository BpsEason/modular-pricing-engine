<?php

namespace App\Domain\Price\Contracts;

use App\Domain\Price\DTO\PriceContext;

interface PriceModifierInterface
{
    /**
     * 應用價格修改邏輯。
     *
     * @param PriceContext $context 當前的價格上下文
     * @return PriceContext 經過修改後的價格上下文
     */
    public function apply(PriceContext $context): PriceContext;

    /**
     * 取得此修飾器的應用優先級。
     * 數值越小，優先級越高，越先被應用。
     *
     * @return int
     */
    public function getPriority(): int;
}

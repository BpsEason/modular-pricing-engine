<?php

namespace App\Domain\Price\Contracts;

use App\Domain\Price\DTO\PriceContext;

interface PriceModifierInterface
{
    /**
     * 應用價格修改到給定的價格上下文。
     *
     * @param PriceContext $context 當前的價格上下文。
     * @return PriceContext 修改後的價格上下文。
     */
    public function apply(PriceContext $context): PriceContext;

    /**
     * 獲取此修飾器的優先級。值越低，越優先被應用。
     *
     * @return int
     */
    public function getPriority(): int;
}

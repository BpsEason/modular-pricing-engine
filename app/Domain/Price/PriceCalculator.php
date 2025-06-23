<?php

namespace App\Domain\Price;

use App\Domain\Price\DTO\PriceContext;
use App\Domain\Price\Contracts\PriceModifierInterface;

class PriceCalculator
{
    /**
     * @var PriceModifierInterface[]
     */
    protected array $modifiers;

    /**
     * PriceCalculator 建構函式。
     *
     * @param PriceModifierInterface[] $modifiers 價格修飾器陣列。
     */
    public function __construct(array $modifiers)
    {
        // 根據優先級對修飾器進行排序 (優先級越低越先應用)
        usort($modifiers, function (PriceModifierInterface $a, PriceModifierInterface $b) {
            return $a->getPriority() <=> $b->getPriority();
        });
        $this->modifiers = $modifiers;
    }

    /**
     * 透過應用所有註冊的修飾器來計算最終價格。
     *
     * @param PriceContext $context 初始價格上下文。
     * @return PriceContext 包含所有修改的最終價格上下文。
     */
    public function calculate(PriceContext $context): PriceContext
    {
        foreach ($this->modifiers as $modifier) {
            $context = $modifier->apply($context);
        }
        return $context;
    }
}

<?php

namespace App\Domain\Price;

use App\Domain\Price\Contracts\PriceModifierInterface;
use App\Domain\Price\DTO\PriceContext;
use Illuminate\Support\Collection;

class PriceCalculator
{
    /**
     * @var Collection<int, PriceModifierInterface>
     */
    private Collection $modifiers;

    /**
     * 根據提供的修飾器初始化價格計算器。
     * 修飾器會根據其 getPriority() 方法的返回值自動排序 (值越小，優先級越高)。
     *
     * @param array<PriceModifierInterface> $modifiers
     */
    public function __construct(array $modifiers)
    {
        $this->modifiers = collect($modifiers)->sortBy(fn(PriceModifierInterface $modifier) => $modifier->getPriority());
    }

    /**
     * 對給定的價格上下文應用所有修飾器進行價格計算。
     *
     * @param PriceContext $context
     * @return PriceContext
     */
    public function calculate(PriceContext $context): PriceContext
    {
        foreach ($this->modifiers as $modifier) {
            $context = $modifier->apply($context);
        }

        // 確保最終價格不會低於零
        if ($context->getCurrentPrice() < 0) {
            // 如果最終價格為負，將差額作為最後的調整
            $finalAdjustment = -$context->getCurrentPrice();
            $context->addModification('final_floor_adjustment', $finalAdjustment);
        }

        return $context;
    }
}

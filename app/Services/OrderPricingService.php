<?php

namespace App\Services;

use App\Domain\Price\PriceCalculator;
use App\Domain\Price\DTO\PriceContext;
use App\Models\User; // 假設你有一個 User 模型來獲取用戶資訊

class OrderPricingService
{
    protected PriceCalculator $priceCalculator;

    public function __construct(PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * 計算訂單的最終價格。
     *
     * @param float $originalPrice 訂單的初始總價。
     * @param array $items 訂單商品項目陣列 (例如：['id' => 1, 'price' => 100, 'qty' => 1, 'is_flash_sale' => true])
     * @param User|null $user 認證用戶 (可選)。
     * @param string|null $paymentMethod 選擇的支付方式 (可選)。
     * @param string|null $couponCode 已應用的優惠券代碼 (可選)。
     * @return PriceContext 包含計算結果的價格上下文。
     */
    public function calculateOrderPrice(
        float $originalPrice,
        array $items,
        ?User $user = null,
        ?string $paymentMethod = null,
        ?string $couponCode = null
    ): PriceContext {
        $context = new PriceContext(
            originalPrice: $originalPrice,
            items: $items,
            user: $user,
            paymentMethod: $paymentMethod,
            couponCode: $couponCode
        );

        return $this->priceCalculator->calculate($context);
    }
}

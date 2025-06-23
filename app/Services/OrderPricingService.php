<?php

namespace App\Services;

use App\Domain\Price\PriceCalculator;
use App\Domain\Price\DTO\PriceContext;
use App\Domain\Price\Contracts\NotifierInterface;

class OrderPricingService
{
    private PriceCalculator $priceCalculator;
    private NotifierInterface $notifier;

    public function __construct(PriceCalculator $priceCalculator, NotifierInterface $notifier)
    {
        $this->priceCalculator = $priceCalculator;
        $this->notifier = $notifier;
    }

    /**
     * 計算訂單最終價格並發送通知。
     *
     * @param float $originalPrice 原始價格
     * @param array $items 商品列表 (可選)
     * @param array $options 額外選項，如 userId, couponCode, paymentMethod
     * @return PriceContext
     */
    public function calculateOrderPrice(float $originalPrice, array $items = [], array $options = []): PriceContext
    {
        $context = new PriceContext(
            originalPrice: $originalPrice,
            items: $items,
            userId: $options['user_id'] ?? null,
            couponCode: $options['coupon_code'] ?? null,
            paymentMethod: $options['payment_method'] ?? null
        );

        $finalContext = $this->priceCalculator->calculate($context);

        # 發送通知（例如：訂單價格計算完成通知）
        $this->notifier->send("Order price calculation completed. Final price: " . $finalContext->getCurrentPrice());

        return $finalContext;
    }
}

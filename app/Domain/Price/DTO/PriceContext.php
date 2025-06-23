<?php

namespace App\Domain\Price\DTO;

class PriceContext
{
    public float $originalPrice;          // 原始訂單總價
    public float $currentPrice;           // 當前計算中的價格
    public array $appliedModifications;   // 已應用的修改 (例如：['coupon' => -100, 'member_discount' => -50])
    public array $items;                  // 訂單中的商品項目 (包含必要的屬性，如 'is_flash_sale')
    public ?object $user;                 // 當前認證用戶 (例如 Eloquent User model，用於會員等級)
    public ?string $paymentMethod;        // 支付方式 (例如 'credit_card', 'line_pay')
    public ?string $couponCode;           // 已應用的優惠券代碼

    public function __construct(
        float $originalPrice,
        array $items,
        ?object $user = null,
        ?string $paymentMethod = null,
        ?string $couponCode = null
    ) {
        $this->originalPrice = $originalPrice;
        $this->currentPrice = $originalPrice; // 從原始價格開始計算
        $this->appliedModifications = [];
        $this->items = $items;
        $this->user = $user;
        $this->paymentMethod = $paymentMethod;
        $this->couponCode = $couponCode;
    }

    /**
     * 添加一個價格修改並更新當前價格。
     *
     * @param string $type 修改的類型 (例如 'coupon_discount', 'member_discount')
     * @param float $amount 修改的金額 (負數表示折扣)
     */
    public function addModification(string $type, float $amount): void
    {
        $this->appliedModifications[$type] = ($this->appliedModifications[$type] ?? 0) + $amount;
        $this->currentPrice += $amount; // 將修改金額加到當前價格上
    }

    // 你可以根據需要添加更多的 getter/setter 或輔助方法
}

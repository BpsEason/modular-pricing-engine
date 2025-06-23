<?php

namespace App\Domain\Price\DTO;

use Illuminate\Support\Collection;

class PriceContext
{
    public float $originalPrice;
    public float $currentPrice;
    public Collection $modifications; // 儲存已應用的修改

    // 可以根據實際需求包含更多數據，例如商品列表、用戶資訊等
    public Collection $items;
    public ?int $userId;
    public ?string $couponCode;
    public ?string $paymentMethod;

    public function __construct(float $originalPrice, array $items = [], ?int $userId = null, ?string $couponCode = null, ?string $paymentMethod = null)
    {
        $this->originalPrice = $originalPrice;
        $this->currentPrice = $originalPrice;
        $this->modifications = new Collection();
        $this->items = new Collection($items);
        $this->userId = $userId;
        $this->couponCode = $couponCode;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * 添加一個價格修改，並更新當前價格。
     *
     * @param string $key 修改的標識（例如：'flash_sale', 'member_discount'）
     * @param float $amount 修改的金額（正值表示增加，負值表示減少）
     * @return void
     */
    public function addModification(string $key, float $amount): void
    {
        $this->modifications->put($key, $amount);
        $this->currentPrice += $amount;
        // 確保價格不會低於零
        if ($this->currentPrice < 0) {
            $this->currentPrice = 0.0;
        }
    }

    /**
     * 獲取所有已應用的修改。
     *
     * @return Collection<string, float>
     */
    public function getModifications(): Collection
    {
        return $this->modifications;
    }

    /**
     * 獲取指定修改的金額。
     *
     * @param string $key
     * @return float|null
     */
    public function getModification(string $key): ?float
    {
        return $this->modifications->get($key);
    }

    /**
     * 獲取訂單項目。
     *
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    // 可以添加更多 getter 方法來訪問其他屬性
    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }

    public function getCurrentPrice(): float
    {
        return $this->currentPrice;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }
}

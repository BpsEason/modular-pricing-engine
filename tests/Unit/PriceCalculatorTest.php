<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Price\PriceCalculator;
use App\Domain\Price\DTO\PriceContext;
use App\Domain\Price\Modifiers\MemberDiscountModifier;
use App\Domain\Price\Modifiers\FlashSaleModifier;
use App\Domain\Price\Modifiers\CouponModifier;
use App\Domain\Price\Modifiers\PaymentMethodModifier;
use App\Models\User; // 假設你有一個 User 模型來模擬會員等級
use Carbon\Carbon; // 引入 Carbon

class PriceCalculatorTest extends TestCase
{
    /** @test */
    public function it_calculates_price_without_any_modifiers()
    {
        $calculator = new PriceCalculator([]); // 沒有修飾器
        $context = new PriceContext(originalPrice: 500.0, items: []);

        $finalContext = $calculator->calculate($context);

        $this->assertEquals(500.0, $finalContext->currentPrice);
        $this->assertEmpty($finalContext->appliedModifications);
    }

    /** @test */
    public function it_applies_member_discount_correctly()
    {
        $user = new User();
        $user->member_level = 'gold'; // 5% discount

        $modifiers = [new MemberDiscountModifier()];
        $calculator = new PriceCalculator($modifiers);
        $context = new PriceContext(originalPrice: 200.0, items: [], user: $user);

        $finalContext = $calculator->calculate($context);

        $this->assertEquals(190.0, $finalContext->currentPrice); // 200 * 0.95 = 190
        $this->assertArrayHasKey('member_discount', $finalContext->appliedModifications);
        $this->assertEquals(-10.0, $finalContext->appliedModifications['member_discount']);
    }

    /** @test */
    public function it_applies_flash_sale_discount_correctly()
    {
        // 為了測試，模擬一個閃購商品和活動時間
        $this->travelTo(Carbon::parse('2025-06-23 12:00:00'));

        $modifiers = [new FlashSaleModifier()];
        $calculator = new PriceCalculator($modifiers);
        $context = new PriceContext(
            originalPrice: 200.0,
            items: [['id' => 1, 'price' => 200, 'qty' => 1, 'is_flash_sale' => true]]
        );

        $finalContext = $calculator->calculate($context);

        $this->assertEquals(170.0, $finalContext->currentPrice); // 200 * 0.85 = 170
        $this->assertArrayHasKey('flash_sale', $finalContext->appliedModifications);
        $this->assertEquals(-30.0, $finalContext->appliedModifications['flash_sale']);

        $this->travelBack(); // 恢復時間
    }

    /** @test */
    public function it_applies_coupon_fixed_amount_correctly()
    {
        $modifiers = [new CouponModifier()];
        $calculator = new PriceCalculator($modifiers);
        $context = new PriceContext(originalPrice: 150.0, items: [], couponCode: 'SAVE20');

        $finalContext = $calculator->calculate($context);

        $this->assertEquals(130.0, $finalContext->currentPrice); // 150 - 20 = 130
        $this->assertArrayHasKey('coupon_discount', $finalContext->appliedModifications);
        $this->assertEquals(-20.0, $finalContext->appliedModifications['coupon_discount']);
    }

    /** @test */
    public function it_applies_payment_method_discount_correctly()
    {
        $modifiers = [new PaymentMethodModifier()];
        $calculator = new PriceCalculator($modifiers);
        $context = new PriceContext(originalPrice: 500.0, items: [], paymentMethod: 'line_pay');

        $finalContext = $calculator->calculate($context);

        $this->assertEquals(490.0, $finalContext->currentPrice); // 500 * 0.98 = 490
        $this->assertArrayHasKey('payment_method_discount', $finalContext->appliedModifications);
        $this->assertEquals(-10.0, $finalContext->appliedModifications['payment_method_discount']);
    }


    /** @test */
    public function it_applies_multiple_modifiers_correctly_in_priority_order()
    {
        // 確保 User model 存在且有 member_level 屬性
        $user = new User(['member_level' => 'gold']); // 5% discount

        // 設置閃購時間以啟用 FlashSaleModifier
        $this->travelTo(Carbon::parse('2025-06-23 12:00:00'));

        $modifiers = [
            new MemberDiscountModifier(),
            new FlashSaleModifier(),
            new CouponModifier(),
            new PaymentMethodModifier(),
        ];
        $calculator = new PriceCalculator($modifiers);

        $context = new PriceContext(
            originalPrice: 1000.0,
            items: [['id' => 1, 'price' => 1000, 'qty' => 1, 'is_flash_sale' => true]],
            user: $user,
            couponCode: 'SAVE20', // 固定金額 20
            paymentMethod: 'line_pay' // 2% 折扣
        );

        $finalContext = $calculator->calculate($context);

        // 預期計算流程 (根據 config/pricing.php 的優先級排序):
        // 原始價格: 1000
        // 1. FlashSale (priority 100, 15%): 1000 - (1000 * 0.15) = 850.0  (修改: -150)
        // 2. Coupon (priority 150, 固定 -20): 850.0 - 20 = 830.0           (修改: -20)
        // 3. Member (priority 200, 5% of current price 830): 830 * 0.05 = 41.5 -> 830 - 41.5 = 788.5 (修改: -41.5)
        // 4. Payment Method (priority 300, 2% of current price 788.5): 788.5 * 0.02 = 15.77 -> 788.5 - 15.77 = 772.73 (修改: -15.77)

        $this->assertEqualsWithDelta(772.73, $finalContext->currentPrice, 0.01);
        $this->assertArrayHasKey('flash_sale', $finalContext->appliedModifications);
        $this->assertArrayHasKey('coupon_discount', $finalContext->appliedModifications);
        $this->assertArrayHasKey('member_discount', $finalContext->appliedModifications);
        $this->assertArrayHasKey('payment_method_discount', $finalContext->appliedModifications);

        $this->assertEqualsWithDelta(-150.0, $finalContext->appliedModifications['flash_sale'], 0.01);
        $this->assertEqualsWithDelta(-20.0, $finalContext->appliedModifications['coupon_discount'], 0.01);
        $this->assertEqualsWithDelta(-41.5, $finalContext->appliedModifications['member_discount'], 0.01);
        $this->assertEqualsWithDelta(-15.77, $finalContext->appliedModifications['payment_method_discount'], 0.01);

        $this->travelBack(); // 恢復時間
    }
}

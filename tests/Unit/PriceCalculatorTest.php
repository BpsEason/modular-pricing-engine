<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Price\PriceCalculator;
use App\Domain\Price\DTO\PriceContext;
use App\Domain\Price\Modifiers\CouponModifier;
use App\Domain\Price\Modifiers\FlashSaleModifier;
use App\Domain\Price\Modifiers\MemberDiscountModifier;
use App\Domain\Price\Modifiers\PaymentMethodModifier;
use App\Domain\Price\Modifiers\HolidayDiscountModifier;
use Carbon\Carbon;

class PriceCalculatorTest extends TestCase
{
    /**
     * 測試 PriceCalculator 是否正確應用所有修飾器。
     */
    public function test_price_calculator_applies_all_modifiers_in_correct_order()
    {
        // 模擬時間為閃購和節日折扣活躍期間
        $this->travelTo(Carbon::parse('2025-01-01 12:00:00'));

        $modifiers = [
            new MemberDiscountModifier(),
            new CouponModifier(),
            new FlashSaleModifier(),
            new PaymentMethodModifier(),
            new HolidayDiscountModifier(),
        ];

        $calculator = new PriceCalculator($modifiers);

        $context = new PriceContext(
            originalPrice: 1000.0,
            userId: 1, // 觸發會員折扣
            couponCode: 'SAVE20', // 觸發固定金額優惠券
            paymentMethod: 'credit_card_visa' // 觸發支付方式回饋
        );

        $finalContext = $calculator->calculate($context);

        // 預期計算步驟（約略值，實際請以代碼邏輯為準）：
        // 原始價格：1000.0
        // 1. FlashSaleModifier (priority 100): 1000 * 0.2 = 200 折扣，剩餘 800
        // 2. HolidayDiscountModifier (priority 120): 800 * 0.1 = 80 折扣，剩餘 720
        // 3. CouponModifier (priority 150): 720 - 20 = 700
        // 4. MemberDiscountModifier (priority 180): 700 * 0.05 = 35 折扣，剩餘 665
        // 5. PaymentMethodModifier (priority 200): 665 * 0.02 = 13.3 折扣，剩餘 651.7
        $this->assertEquals(651.7, round($finalContext->currentPrice, 1));
        $this->assertCount(5, $finalContext->modifications);
        $this->assertEquals(-200.0, $finalContext->getModification('flash_sale'));
        $this->assertEquals(-80.0, $finalContext->getModification('holiday_discount'));
        $this->assertEquals(-20.0, $finalContext->getModification('coupon_discount'));
        $this->assertEquals(-35.0, $finalContext->getModification('member_discount'));
        $this->assertEquals(-13.3, round($finalContext->getModification('payment_method_discount'), 1));

        $this->travelBack(); // 恢復時間
    }

    /**
     * 測試單獨應用閃購修飾器。
     */
    public function test_flash_sale_modifier_applies_correctly()
    {
        $this->travelTo(Carbon::parse('2025-01-01 12:00:00'));
        $calculator = new PriceCalculator([new FlashSaleModifier()]);
        $context = new PriceContext(originalPrice: 100.0);
        $finalContext = $calculator->calculate($context);
        $this->assertEquals(80.0, $finalContext->currentPrice);
        $this->assertEquals(-20.0, $finalContext->getModification('flash_sale'));
        $this->travelBack();
    }

    /**
     * 測試當前不在閃購時間時，閃購修飾器不生效。
     */
    public function test_flash_sale_modifier_does_not_apply_outside_active_time()
    {
        $this->travelTo(Carbon::parse('2025-01-02 12:00:00'));
        $calculator = new PriceCalculator([new FlashSaleModifier()]);
        $context = new PriceContext(originalPrice: 100.0);
        $finalContext = $calculator->calculate($context);
        $this->assertEquals(100.0, $finalContext->currentPrice);
        $this->assertNull($finalContext->getModification('flash_sale'));
        $this->travelBack();
    }

    /**
     * 測試優惠券修飾器是否正確應用。
     */
    public function test_coupon_modifier_applies_correctly()
    {
        $calculator = new PriceCalculator([new CouponModifier()]);
        $context = new PriceContext(originalPrice: 200.0, couponCode: 'SAVE20');
        $finalContext = $calculator->calculate($context);
        $this->assertEquals(180.0, $finalContext->currentPrice);
        $this->assertEquals(-20.0, $finalContext->getModification('coupon_discount'));

        $context = new PriceContext(originalPrice: 100.0, couponCode: 'PERCENT10');
        $finalContext = $calculator->calculate($context);
        $this->assertEquals(90.0, $finalContext->currentPrice);
        $this->assertEquals(-10.0, $finalContext->getModification('coupon_discount'));
    }

    /**
     * 測試會員折扣修飾器是否正確應用。
     */
    public function test_member_discount_modifier_applies_correctly()
    {
        $calculator = new PriceCalculator([new MemberDiscountModifier()]);
        $context = new PriceContext(originalPrice: 100.0, userId: 1);
        $finalContext = $calculator->calculate($context);
        $this->assertEquals(95.0, $finalContext->currentPrice);
        $this->assertEquals(-5.0, $finalContext->getModification('member_discount'));
    }

    /**
     * 測試支付方式回饋修飾器是否正確應用。
     */
    public function test_payment_method_modifier_applies_correctly()
    {
        $calculator = new PriceCalculator([new PaymentMethodModifier()]);
        $context = new PriceContext(originalPrice: 100.0, paymentMethod: 'credit_card_visa');
        $finalContext = $calculator->calculate($context);
        $this->assertEquals(98.0, $finalContext->currentPrice);
        $this->assertEquals(-2.0, $finalContext->getModification('payment_method_discount'));
    }

    /**
     * 測試節日折扣修飾器是否正確應用。
     */
    public function test_holiday_discount_modifier_applies_correctly()
    {
        $this->travelTo(Carbon::parse('2025-12-25'));
        $calculator = new PriceCalculator([new HolidayDiscountModifier()]);
        $context = new PriceContext(originalPrice: 200.0, items: []);
        $finalContext = $calculator->calculate($context);
        $this->assertEquals(180.0, $finalContext->currentPrice);
        $this->assertEquals(-20.0, $finalContext->getModification('holiday_discount'));
        $this->travelBack();
    }

    /**
     * 測試價格計算器處理負折扣不應導致負總價，並記錄最終調整。
     */
    public function test_price_calculator_does_not_result_in_negative_price()
    {
        // 創建一個會導致價格為負的修飾器
        $superDiscountModifier = new class implements \App\Domain\Price\Contracts\PriceModifierInterface {
            public function apply(PriceContext $context): PriceContext
            {
                // 給一個遠超原始價格的折扣
                $context->addModification('super_discount', -$context->currentPrice * 2);
                return $context;
            }
            public function getPriority(): int { return 50; }
        };

        $calculator = new PriceCalculator([$superDiscountModifier]);
        $context = new PriceContext(originalPrice: 10.0);
        $finalContext = $calculator->calculate($context);

        $this->assertEquals(0.0, $finalContext->currentPrice);
        $this->assertEquals(-20.0, $finalContext->getModification('super_discount')); // 原始折扣值
        $this->assertEquals(10.0, $finalContext->getModification('final_floor_adjustment')); // 最終價格被調整到 0
    }
}

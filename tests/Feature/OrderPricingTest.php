<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class OrderPricingTest extends TestCase
{
    // 如果你的功能測試需要資料庫，請打開 RefreshDatabase
    // use RefreshDatabase; 

    /**
     * 測試計算訂單價格 API 的基本功能。
     */
    public function test_calculate_order_price_api_basic_functionality()
    {
        // 模擬時間為閃購和節日折扣活躍期間
        $this->travelTo(Carbon::parse('2025-01-01 12:00:00'));

        $response = $this->postJson('/api/calculate-order-price', [
            'original_price' => 1000.0,
            'user_id' => 1, // 觸發會員折扣
            'coupon_code' => 'SAVE20', // 觸發固定金額優惠券
            'payment_method' => 'credit_card_visa', // 觸發支付方式回饋
            'items' => [
                ['id' => 1, 'price' => 500, 'qty' => 1],
                ['id' => 2, 'price' => 250, 'qty' => 2],
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'original_price',
                     'final_price',
                     'modifications',
                     'message'
                 ])
                 ->assertJson([
                     'original_price' => 1000.0,
                     'final_price' => 651.7,
                     'modifications' => [
                         'flash_sale' => -200.0,
                         'holiday_discount' => -80.0,
                         'coupon_discount' => -20.0,
                         'member_discount' => -35.0,
                         'payment_method_discount' => -13.3,
                     ]
                 ]);

        $this->travelBack(); // 恢復時間
    }

    /**
     * 測試 API 驗證失敗的情況。
     */
    public function test_calculate_order_price_api_validation_fails()
    {
        $response = $this->postJson('/api/calculate-order-price', [
            'original_price' => 'not-a-number', // 無效輸入
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['original_price']);
    }

    /**
     * 測試沒有任何折扣應用時的價格計算。
     */
    public function test_calculate_order_price_api_no_modifiers_applied()
    {
        // 選擇一個沒有折扣活動的時間
        $this->travelTo(Carbon::parse('2025-02-01 12:00:00'));

        $response = $this->postJson('/api/calculate-order-price', [
            'original_price' => 500.0,
            // 不提供任何觸發折扣的參數
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'original_price' => 500.0,
                     'final_price' => 500.0,
                     'modifications' => [] // 應為空陣列
                 ]);

        $this->travelBack(); // 恢復時間
    }
}

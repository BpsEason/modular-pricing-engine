<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; // 確保你的專案中有這個模型
use Carbon\Carbon; // 引入 Carbon

class OrderPricingTest extends TestCase
{
    use RefreshDatabase; // 如果你的測試會用到資料庫，建議使用

    /**
     * 在測試前執行資料庫遷移和填充 (如果 RefreshDatabase 被使用)。
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        // 創建一個測試用戶，如果你的 OrderController 需要 auth()->user()
        // User::factory()->create(['member_level' => 'gold']);
    }

    /** @test */
    public function a_guest_can_calculate_price_without_discounts()
    {
        $response = $this->postJson('/api/calculate-order-price', [
            'original_price' => 1000.0,
            'items' => [['id' => 1, 'name' => 'Test Item', 'price' => 1000, 'qty' => 1]]
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'original_price' => 1000.0,
                     'final_price' => 1000.0,
                     'modifications' => []
                 ])
                 ->assertJsonPath('context.user_id', null); // 確保未傳入 user_id 時為 null
    }

    /** @test */
    public function a_member_gets_discount_on_price_calculation()
    {
        // 為了這個測試，創建一個有會員等級的用戶
        $user = User::factory()->create(['member_level' => 'gold']);

        $response = $this->postJson('/api/calculate-order-price', [
            'original_price' => 1000.0,
            'items' => [['id' => 1, 'name' => 'Test Item', 'price' => 1000, 'qty' => 1]],
            'user_id' => $user->id, // 傳遞用戶 ID
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'original_price' => 1000.0,
                     'final_price' => 950.0, // 1000 * (1 - 0.05) = 950 (假設只有會員折扣生效)
                     'modifications' => [
                         'member_discount' => -50.0
                     ]
                 ])
                 ->assertJsonPath('context.user_id', $user->id)
                 ->assertJsonPath('context.user_member_level', 'gold');
    }

    /** @test */
    public function it_applies_multiple_modifiers_correctly_via_api()
    {
        // 為了這個測試，創建一個有會員等級的用戶
        $user = User::factory()->create(['member_level' => 'gold']);

        // 為了觸發 FlashSaleModifier，需要將當前時間設定在閃購期間
        $this->travelTo(Carbon::parse('2025-06-23 12:00:00'));

        $response = $this->postJson('/api/calculate-order-price', [
            'original_price' => 1000.0,
            'items' => [['id' => 1, 'name' => 'Flash Item', 'price' => 1000, 'qty' => 1, 'is_flash_sale' => true]],
            'coupon_code' => 'SAVE20', // 使用固定金額優惠券
            'payment_method' => 'line_pay', // 使用 Line Pay
            'user_id' => $user->id,
        ]);

        // 預期結果會複雜，依賴於 config/pricing.php 中的順序和各 Modifier 的邏輯
        // 詳細計算步驟參考 PriceCalculatorTest 中的解釋
        $response->assertStatus(200)
                 ->assertJson([
                     'original_price' => 1000.0,
                     'final_price' => 772.73, // 驗證最終金額
                     'modifications' => [
                         'flash_sale' => -150.0,
                         'coupon_discount' => -20.0,
                         'member_discount' => -41.5,
                         'payment_method_discount' => -15.77,
                     ]
                 ]);

        $this->travelBack(); // 恢復時間
    }
}

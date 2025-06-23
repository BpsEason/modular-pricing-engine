<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderPricingService;
use App\Models\User; // 假設你有一個 User 模型來模擬會員等級

class OrderController extends Controller
{
    protected OrderPricingService $orderPricingService;

    public function __construct(OrderPricingService $orderPricingService)
    {
        $this->orderPricingService = $orderPricingService;
    }

    /**
     * 計算訂單價格的 API 接口。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculatePrice(Request $request)
    {
        // 基礎驗證 (可根據需求擴展為 Form Request)
        $request->validate([
            'original_price' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*.id' => 'required',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
            'user_id' => 'nullable|exists:users,id', // 假設你需要從 DB 獲取真實用戶
            'coupon_code' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        $originalPrice = (float) $request->input('original_price');
        $items = $request->input('items');
        $couponCode = $request->input('coupon_code');
        $paymentMethod = $request->input('payment_method');

        // 模擬用戶資訊
        // 實際應用中，這裡會是從認證系統 (auth()->user()) 或資料庫查詢真實用戶
        $user = null;
        if ($request->filled('user_id')) {
            $user = User::find($request->input('user_id'));
            // 如果用戶不存在，可以拋出錯誤或繼續以匿名用戶處理
        } else {
            // 為了演示，這裡創建一個模擬用戶，你可以根據需要調整其 member_level
            $mockUser = new User();
            $mockUser->id = 999;
            $mockUser->member_level = 'gold'; // 預設為黃金會員
            $user = $mockUser;
        }


        $priceContext = $this->orderPricingService->calculateOrderPrice(
            $originalPrice,
            $items,
            $user,
            $paymentMethod,
            $couponCode
        );

        return response()->json([
            'original_price' => $priceContext->originalPrice,
            'final_price' => round($priceContext->currentPrice, 2), // 四捨五入到小數點兩位
            'modifications' => $priceContext->appliedModifications,
            'details' => 'Price calculated successfully with applied modifiers.',
            'context' => [ // 可選：顯示更多上下文信息用於調試
                'user_id' => $user ? $user->id : null,
                'user_member_level' => $user ? ($user->member_level ?? 'N/A') : null,
                'coupon_code_used' => $priceContext->couponCode,
                'payment_method_used' => $priceContext->paymentMethod,
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\OrderPricingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private OrderPricingService $orderPricingService;

    public function __construct(OrderPricingService $orderPricingService)
    {
        $this->orderPricingService = $orderPricingService;
    }

    /**
     * 計算訂單價格。
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'original_price' => 'required|numeric|min:0',
                'items' => 'nullable|array',
                'user_id' => 'nullable|integer',
                'coupon_code' => 'nullable|string',
                'payment_method' => 'nullable|string',
            ]);

            $originalPrice = $validated['original_price'];
            $items = $validated['items'] ?? [];
            $options = [
                'user_id' => $validated['user_id'] ?? null,
                'coupon_code' => $validated['coupon_code'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
            ];

            $finalContext = $this->orderPricingService->calculateOrderPrice($originalPrice, $items, $options);

            return response()->json([
                'original_price' => $finalContext->getOriginalPrice(),
                'final_price' => $finalContext->getCurrentPrice(),
                'modifications' => $finalContext->getModifications()->toArray(),
                'message' => 'Price calculated successfully.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Price calculation error: " . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during price calculation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

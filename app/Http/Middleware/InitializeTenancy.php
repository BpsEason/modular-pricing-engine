<?php

// app/Http/Middleware/InitializeTenancy.php
// 如果沒有使用多租戶套件，這個檔案可以刪除。這只是為了確保基本的 Laravel 結構完整。

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 這裡通常會是初始化多租戶環境的邏輯
        // 例如：根據域名或請求頭識別租戶，並切換資料庫連接等。
        // 對於這個專案，我們暫時不實現多租戶。
        return $next($request);
    }
}

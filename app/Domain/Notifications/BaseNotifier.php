<?php

namespace App\Domain\Notifications;

use App\Domain\Notifications\Contracts\Notifier;
use Illuminate\Support\Facades\Log; // 引入 Laravel 的 Log Facade

class BaseNotifier implements Notifier
{
    /**
     * 基礎通知發送方法，通常只是簡單記錄日誌或不執行實際操作。
     * 實際應用中，這可能是發送 Email 的基底或僅用於測試。
     */
    public function send(string $recipient, string $message): void
    {
        Log::info("[BaseNotifier] Sending to {$recipient} message: '{$message}'");
        // 這裡可以放置實際的通知發送代碼，例如透過 Laravel 的 Mailer 或 SMS 服務
        // Mail::to($recipient)->send(new SomeMailable($message));
    }
}

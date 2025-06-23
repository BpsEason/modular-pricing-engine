<?php

namespace App\Domain\Price\Notifiers;

use App\Domain\Price\Contracts\NotifierInterface;
use Illuminate\Support\Facades\Log; // 引入 Log Facade
use Illuminate\Support\Facades\Mail; // 引入 Mail Facade (如果實際會發郵件)

class EmailNotifier implements NotifierInterface
{
    public function send(string $message): void
    {
        // 實際發送郵件的邏輯 (例如使用 Laravel 的 Mail Facade)
        Log::info("[EmailNotifier] Sending Email: " . $message);
        // 如果要實際發送，需要設定郵件配置並可能創建一個 Mailable
        // try {
        //     Mail::to('admin@example.com')->send(new SomeNotificationMail($message));
        // } catch (\Exception $e) {
        //     Log::error("Failed to send email notification: " . $e->getMessage());
        // }
    }
}

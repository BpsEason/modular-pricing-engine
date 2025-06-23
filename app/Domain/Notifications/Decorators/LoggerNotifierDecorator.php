<?php

namespace App\Domain\Notifications\Decorators;

use App\Domain\Notifications\Contracts\Notifier;
use Illuminate\Support\Facades\Log;

class LoggerNotifierDecorator implements Notifier
{
    protected Notifier $notifier;

    /**
     * LoggerNotifierDecorator 建構函式。
     *
     * @param Notifier $notifier 被裝飾的通知器實例。
     */
    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * 發送通知，並在發送前後記錄日誌。
     */
    public function send(string $recipient, string $message): void
    {
        Log::info("[LoggerDecorator] Logging before sending to {$recipient}: '{$message}'");
        $this->notifier->send($recipient, $message); // 調用被裝飾通知器的發送方法
        Log::info("[LoggerDecorator] Logging after sending to {$recipient}.");
    }
}

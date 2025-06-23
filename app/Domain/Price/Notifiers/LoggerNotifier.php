<?php

namespace App\Domain\Price\Notifiers;

use App\Domain\Price\Contracts\NotifierInterface;

class LoggerNotifier extends NotifierDecorator
{
    public function send(string $message): void
    {
        parent::send($message); # 先執行被裝飾者的發送邏輯
        \Log::info("[LoggerNotifier] Logged Notification: " . $message); # 增加日誌功能
    }
}

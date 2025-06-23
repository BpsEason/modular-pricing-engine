<?php

namespace App\Domain\Notifications\Contracts;

interface Notifier
{
    /**
     * 發送通知。
     *
     * @param string $recipient 接收者 (例如 Email 地址、電話號碼)
     * @param string $message 通知內容
     */
    public function send(string $recipient, string $message): void;
}

<?php

namespace App\Domain\Price\Notifiers;

use App\Domain\Price\Contracts\NotifierInterface;

abstract class NotifierDecorator implements NotifierInterface
{
    protected NotifierInterface $notifier;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function send(string $message): void
    {
        $this->notifier->send($message);
    }
}

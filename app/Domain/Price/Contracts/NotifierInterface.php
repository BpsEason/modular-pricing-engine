<?php

namespace App\Domain\Price\Contracts;

interface NotifierInterface
{
    public function send(string $message): void;
}

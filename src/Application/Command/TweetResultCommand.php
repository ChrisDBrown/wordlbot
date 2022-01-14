<?php

declare(strict_types=1);

namespace App\Application\Command;

final class TweetResultCommand
{
    public function __construct(private string $result)
    {
    }

    public function getResult(): string
    {
        return $this->result;
    }
}

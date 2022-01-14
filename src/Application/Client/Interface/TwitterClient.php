<?php

declare(strict_types=1);

namespace App\Application\Client\Interface;

interface TwitterClient
{
    public function sendTweet(string $status): void;
}

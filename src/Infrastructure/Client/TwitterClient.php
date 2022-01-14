<?php

declare(strict_types=1);

namespace App\Infrastructure\Client;

use App\Application\Client\Interface\TwitterClient as TwitterClientInterface;
use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;

final class TwitterClient implements TwitterClientInterface
{
    private BirdElephant $client;

    public function __construct(
        string $consumerKey,
        string $consumerSecret,
        string $tokenIdentifier,
        string $tokenSecret
    ) {
        $this->client = new BirdElephant([
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'token_identifier' => $tokenIdentifier,
            'token_secret' => $tokenSecret,
        ]);
    }

    public function sendTweet(string $status): void
    {
        $tweet = (new Tweet())->text($status);

        $this->client->tweets()->tweet($tweet);
    }
}

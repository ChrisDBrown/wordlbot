<?php

declare(strict_types=1);

namespace App\Application\CommandHandler;

use App\Application\Client\Interface\TwitterClient;
use App\Application\Command\TweetResultCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class TweetResultCommandHandler implements MessageHandlerInterface
{
    public function __construct(private TwitterClient $client)
    {
    }

    public function __invoke(TweetResultCommand $command): void
    {
        $this->client->sendTweet($command->getResult());
    }
}

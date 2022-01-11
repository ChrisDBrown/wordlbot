<?php

declare(strict_types=1);

namespace App\Application\QueryHandler;

use App\Application\Query\SuggestSolutionQuery;
use App\Domain\Services\Guesser;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SuggestSolutionQueryHandler implements MessageHandlerInterface
{
    public function __construct(
        private Guesser $guesser
    ) {
    }

    public function __invoke(SuggestSolutionQuery $query): string
    {
        return $this->guesser->guess($query->getResultHistory());
    }
}

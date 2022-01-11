<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Services\Interface\PossibleAnswersFilter;
use App\Domain\Services\Interface\PossibleAnswersProvider;
use App\Domain\Services\Interface\PossibleAnswersRanker;
use App\Domain\ValueObject\ResultHistory;

use function count;

final class Guesser
{
    private const FIRST_GUESS              = 'beast';
    private const CONTINGENCY_SECOND_GUESS = 'round';
    private const CONTINGENCY_THIRD_GUESS  = 'lymph';

    public function __construct(
        private PossibleAnswersProvider $provider,
        private PossibleAnswersFilter $filter,
        private PossibleAnswersRanker $ranker
    ) {
    }

    public function guess(ResultHistory $resultHistory): string
    {
        if (count($resultHistory->getResults()) === 0) {
            return self::FIRST_GUESS;
        }

        if (count($resultHistory->getResults()) === 1 && $resultHistory->hasKnownLetters() === false) {
            return self::CONTINGENCY_SECOND_GUESS;
        }

        if (count($resultHistory->getResults()) === 2 && $resultHistory->hasKnownLetters() === false) {
            return self::CONTINGENCY_THIRD_GUESS;
        }

        $remainingAnswers = $this->filter->getValidAnswersForHistory($this->provider->getAllPossibleAnswers(), $resultHistory);

        return $this->ranker->getHighestRankingPossibleAnswer($remainingAnswers, $resultHistory->getUnguessedLetters());
    }
}

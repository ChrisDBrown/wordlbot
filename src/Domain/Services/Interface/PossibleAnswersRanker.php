<?php

declare(strict_types=1);

namespace App\Domain\Services\Interface;

interface PossibleAnswersRanker
{
    /**
     * @param array<int, string> $possibleAnswers
     * @param array<int, string> $unguessedLetters
     */
    public function getHighestRankingPossibleAnswer(array $possibleAnswers, array $unguessedLetters): string;
}

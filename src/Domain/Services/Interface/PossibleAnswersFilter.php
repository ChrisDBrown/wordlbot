<?php

declare(strict_types=1);

namespace App\Domain\Services\Interface;

use App\Domain\ValueObject\ResultHistory;

interface PossibleAnswersFilter
{
    /**
     * @param array<int, string> $possibleAnswers
     *
     * @return array<int, string>
     */
    public function getValidAnswersForHistory(array $possibleAnswers, ResultHistory $resultHistory): array;
}

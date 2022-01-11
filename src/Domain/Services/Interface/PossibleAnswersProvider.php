<?php

declare(strict_types=1);

namespace App\Domain\Services\Interface;

interface PossibleAnswersProvider
{
    /** @return array<int, string> */
    public function getAllPossibleAnswers(): array;
}

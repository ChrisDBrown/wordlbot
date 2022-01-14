<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\ResultHistory;

final class EvaluationOutcome
{
    public function __construct(private string $answer, private ResultHistory $resultHistory)
    {
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getResultHistory(): ResultHistory
    {
        return $this->resultHistory;
    }
}

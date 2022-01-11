<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class NoPossibleScores extends CannotRankAnswers
{
    public function __construct()
    {
        parent::__construct('No letter scores for unguessed letters, cannot rank answers');
    }
}

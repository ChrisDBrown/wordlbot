<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class NoWinnerFound extends CannotRankAnswers
{
    public function __construct()
    {
        parent::__construct('No winner was found during answer ranking');
    }
}

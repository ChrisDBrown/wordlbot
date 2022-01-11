<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class NoPossibleAnswers extends CannotRankAnswers
{
    public function __construct()
    {
        parent::__construct('Possible answers array is empty, cannot rank answers');
    }
}

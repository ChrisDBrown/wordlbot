<?php

declare(strict_types=1);

namespace App\Application\CommandHandler;

use App\Application\Command\SolveAllWordsCommand;
use App\Application\DTO\EvaluationOutcomes;
use App\Domain\Exception\FilterReturnsEmpty;
use App\Domain\Exception\HistoryLengthExceeded;
use App\Domain\Exception\NoWinnerFound;
use App\Domain\Services\Evaluator;
use App\Domain\Services\Guesser;
use App\Domain\Services\PossibleAnswersProvider;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SolveAllWordsCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private PossibleAnswersProvider $provider,
        private Evaluator $evaluator,
        private Guesser $guesser
    ) {
    }

    public function __invoke(SolveAllWordsCommand $command): EvaluationOutcomes
    {
        $outcomes = new EvaluationOutcomes();

        foreach ($this->provider->getAllPossibleAnswers() as $answer) {
            $resultHistory = new ResultHistory();
            try {
                do {
                    $guess   = $this->guesser->guess($resultHistory);
                    $outcome = $this->evaluator->evaluate($guess, $answer);

                    $resultHistory->addResult(new Result($guess, $outcome));
                } while ($resultHistory->isSolved() === false);

                $outcomes->addPass($answer, $resultHistory);
            } catch (FilterReturnsEmpty | NoWinnerFound | HistoryLengthExceeded $e) {
                $outcomes->addFailure($e::class, $answer, $resultHistory);
            }
        }

        return $outcomes;
    }
}

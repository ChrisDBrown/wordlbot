<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Query\SuggestSolutionQuery;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

use function assert;
use function is_string;
use function sprintf;
use function str_replace;
use function strlen;

final class InteractiveSolverConsole extends Command
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    protected static $defaultName = 'wordlbot:solver';

    public function __construct(
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resultHistory = new ResultHistory();

        $helper = $this->getHelper('question');
        assert($helper instanceof QuestionHelper);

        do {
            $envelope = $this->bus->dispatch(new SuggestSolutionQuery($resultHistory));

            $guess = $envelope->last(HandledStamp::class)->getResult();

            $output->writeln(sprintf('Guess is %s', $guess));

            $question = new Question('Please enter the result (n: no match, l: in word, wrong place, p: in word, correct place): ');
            $question->setValidator(static function ($answer) {
                if (! is_string($answer) || strlen($answer) !== 5) {
                    throw new InvalidArgumentException(
                        'Outcome must be 5 characters long'
                    );
                }

                $filtered = str_replace(['n', 'l', 'p'], '', $answer);

                if ($filtered !== '') {
                    throw new InvalidArgumentException(
                        'Outcome must only contain n, l, and p'
                    );
                }

                return $answer;
            });

            $outcome = $helper->ask($input, $output, $question);

            $resultHistory->addResult(new Result($guess, $outcome));
        } while ($outcome !== 'ppppp');

        $output->writeln('Solved successfully');

        return Command::SUCCESS;
    }
}

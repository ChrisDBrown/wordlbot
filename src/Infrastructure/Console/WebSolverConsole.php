<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Command\TweetResultCommand;
use App\Application\Query\WebSolverQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

use function assert;

final class WebSolverConsole extends Command
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    protected static $defaultName = 'wordlbot:solver:web';

    public function __construct(
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $envelope = $this->bus->dispatch(new WebSolverQuery());
        $result   = $envelope->last(HandledStamp::class)->getResult();

        $output->writeln($result);

        $helper = $this->getHelper('question');
        assert($helper instanceof QuestionHelper);

        $shouldTweetQuestion = new ConfirmationQuestion('Send tweet? y/N', false);

        $shouldTweet = $helper->ask($input, $output, $shouldTweetQuestion);

        if ($shouldTweet === true) {
            $this->bus->dispatch(new TweetResultCommand($result));
        }

        return 0;
    }
}

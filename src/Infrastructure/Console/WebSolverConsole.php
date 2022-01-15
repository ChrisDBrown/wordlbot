<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Command\TweetResultCommand;
use App\Application\Query\WebSolverQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class WebSolverConsole extends Command
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    protected static $defaultName = 'wordlbot:solver:web';
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    protected static $defaultDescription = 'Run the guesser against the live website, and optionally tweet the result (--tweet / -t)';

    public function __construct(
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'tweet',
            't',
            InputOption::VALUE_NONE,
            'Tweet the result',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $envelope = $this->bus->dispatch(new WebSolverQuery());
        $result   = $envelope->last(HandledStamp::class)->getResult();

        $output->writeln($result);

        if ($input->getOption('tweet') === true) {
            $this->bus->dispatch(new TweetResultCommand($result));
            $output->writeln('Result was tweeted');
        }

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Query\SuggestSolutionQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

use function sprintf;

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
        $envelope = $this->bus->dispatch(new SuggestSolutionQuery());

        $output->writeln(sprintf('Query output: %s', $envelope->last(HandledStamp::class)->getResult()));
        $output->writeln('Command ran successfully');

        return Command::SUCCESS;
    }
}

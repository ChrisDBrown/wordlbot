<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Query\WebSolverQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

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
        $this->bus->dispatch(new WebSolverQuery());

        return 0;
    }
}

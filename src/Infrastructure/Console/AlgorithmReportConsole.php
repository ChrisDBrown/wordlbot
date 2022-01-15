<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Command\SolveAllWordsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

use function count;

final class AlgorithmReportConsole extends Command
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    protected static $defaultName = 'wordlbot:report:algorithm';
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string|null
     */
    protected static $defaultDescription = 'Report the results of running the current algorithm over the entire wordlist';

    public function __construct(
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $envelope = $this->bus->dispatch(new SolveAllWordsCommand());

        $outcome = $envelope->last(HandledStamp::class)->getResult();

        $tableRows = [
            ['Passes', count($outcome->getPasses())],
            ['Failures', count($outcome->getAllFailures())],
        ];

        if (count($outcome->getAllFailures()) > 0) {
            $tableRows[] = new TableSeparator();

            foreach ($outcome->getErrorClasses() as $errorClass) {
                $tableRows[] = [$errorClass, count($outcome->getFailuresOfErrorClass($errorClass))];
            }
        }

        $table = new Table($output);
        $table->setRows($tableRows);

        $table->render();

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Services;

use App\Application\Command\SolveAllWordsCommand;
use App\Application\CommandHandler\SolveAllWordsCommandHandler;
use App\Domain\Exception\HistoryLengthExceeded;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function assert;

final class SolveAllWordsCommandHandlerTest extends KernelTestCase
{
    private SolveAllWordsCommandHandler $handler;

    public function setUp(): void
    {
        self::bootKernel();

        $handler = static::getContainer()->get(SolveAllWordsCommandHandler::class);
        assert($handler instanceof SolveAllWordsCommandHandler);

        $this->handler = $handler;

        parent::setUp();
    }

    /**
     * This is to make sure no regressions are introduced when changing the algorithm
     * Update the test on any improvements that mean no days fail
     *
     * @test
     */
    public function shouldPassExpectedNumberOfDays(): void
    {
        $outcome = ($this->handler)(new SolveAllWordsCommand());

        self::assertCount(2283, $outcome->getPasses());
        self::assertCount(32, $outcome->getAllFailures());
        self::assertSame([HistoryLengthExceeded::class], $outcome->getErrorClasses());
        self::assertCount(32, $outcome->getFailuresOfErrorClass(HistoryLengthExceeded::class));
    }
}

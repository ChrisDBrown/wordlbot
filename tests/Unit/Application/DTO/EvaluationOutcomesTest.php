<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\DTO;

use App\Application\DTO\EvaluationOutcomes;
use App\Domain\Exception\FilterReturnsEmpty;
use App\Domain\Exception\HistoryLengthExceeded;
use App\Domain\Exception\NoWinnerFound;
use App\Domain\ValueObject\ResultHistory;
use PHPUnit\Framework\TestCase;

/** @covers */
final class EvaluationOutcomesTest extends TestCase
{
    /** @test */
    public function shouldAddPasses(): void
    {
        $outcomes = new EvaluationOutcomes();

        $outcomes->addPass('brain', new ResultHistory());
        $outcomes->addPass('tangy', new ResultHistory());
        $outcomes->addPass('abbey', new ResultHistory());

        $actual = $outcomes->getPasses();
        self::assertCount(3, $actual);
        self::assertSame('brain', $actual[0]->getAnswer());
        self::assertSame('tangy', $actual[1]->getAnswer());
        self::assertSame('abbey', $actual[2]->getAnswer());
    }

    /** @test */
    public function shouldGetAllFailures(): void
    {
        $outcomes = new EvaluationOutcomes();

        $outcomes->addFailure(FilterReturnsEmpty::class, 'brain', new ResultHistory());
        $outcomes->addFailure(FilterReturnsEmpty::class, 'tangy', new ResultHistory());
        $outcomes->addFailure(FilterReturnsEmpty::class, 'abbey', new ResultHistory());
        $outcomes->addFailure(NoWinnerFound::class, 'beast', new ResultHistory());
        $outcomes->addFailure(NoWinnerFound::class, 'taint', new ResultHistory());
        $outcomes->addFailure(NoWinnerFound::class, 'gavin', new ResultHistory());
        $outcomes->addFailure(HistoryLengthExceeded::class, 'vapor', new ResultHistory());
        $outcomes->addFailure(HistoryLengthExceeded::class, 'gorge', new ResultHistory());
        $outcomes->addFailure(HistoryLengthExceeded::class, 'spine', new ResultHistory());

        self::assertCount(9, $outcomes->getAllFailures());

        self::assertCount(3, $outcomes->getErrorClasses());

        self::assertCount(3, $outcomes->getFailuresOfErrorClass(FilterReturnsEmpty::class));
        self::assertCount(3, $outcomes->getFailuresOfErrorClass(NoWinnerFound::class));
        self::assertCount(3, $outcomes->getFailuresOfErrorClass(HistoryLengthExceeded::class));
    }
}

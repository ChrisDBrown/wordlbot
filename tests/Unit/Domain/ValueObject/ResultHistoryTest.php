<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\HistoryLengthExceeded;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use PHPUnit\Framework\TestCase;

/** @covers ResultHistory */
final class ResultHistoryTest extends TestCase
{
    public function shouldAddResultsInOrder(): void
    {
        $resultHistory = new ResultHistory();

        $one   = new Result('beast', 'nnnnn');
        $two   = new Result('onion', 'nnnnn');
        $three = new Result('quarry', 'nnnnn');

        $resultHistory->addResult($three);
        $resultHistory->addResult($one);
        $resultHistory->addResult($two);

        self::assertSame([$three, $one, $two], $resultHistory->getResults());
    }

    /** @test */
    public function shouldErrorOnAddingTooManyResults(): void
    {
        $resultHistory = new ResultHistory();

        $resultHistory->addResult(new Result('beast', 'nnnnn'));
        $resultHistory->addResult(new Result('onion', 'nnnnn'));
        $resultHistory->addResult(new Result('quart', 'nnnnn'));
        $resultHistory->addResult(new Result('video', 'nnnnn'));
        $resultHistory->addResult(new Result('chair', 'nnnnn'));

        self::expectException(HistoryLengthExceeded::class);
        $resultHistory->addResult(new Result('train', 'nnnnn'));
    }
}

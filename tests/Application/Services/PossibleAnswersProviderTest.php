<?php

declare(strict_types=1);

namespace App\Tests\Application\Services;

use App\Application\Exception\InvalidWordList;
use App\Application\Exception\MissingWordList;
use App\Application\Services\PossibleAnswersProvider;
use PHPUnit\Framework\TestCase;

/** @covers \App\Application\Services\PossibleAnswersProvider */
final class PossibleAnswersProviderTest extends TestCase
{
    /** @test */
    public function shouldThrowOnUnableToOpenFile(): void
    {
        $provider = new PossibleAnswersProvider(__DIR__ . '/nonexistantWordList.json');

        self::expectException(MissingWordList::class);
        $provider->getAllPossibleAnswers();
    }

    /** @test */
    public function shouldThrowOnFileNotValidJson(): void
    {
        $provider = new PossibleAnswersProvider(__DIR__ . '/badWordList.json');

        self::expectException(InvalidWordList::class);
        $provider->getAllPossibleAnswers();
    }

    /** @test */
    public function shouldReturnWordList(): void
    {
        $provider = new PossibleAnswersProvider(__DIR__ . '/goodWordList.json');

        $actual = $provider->getAllPossibleAnswers();

        $expected = [
            'cigar',
            'rebut',
            'sissy',
            'humph',
            'awake',
            'blush',
            'focal',
            'evade',
            'naval',
            'serve',
        ];

        self::assertSame($expected, $actual);
    }
}

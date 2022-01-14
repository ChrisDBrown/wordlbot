<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class InteractiveSolverConsoleTest extends KernelTestCase
{
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel      = self::bootKernel();
        $application = new Application($kernel);

        $command             = $application->find('wordlbot:solver:console');
        $this->commandTester = new CommandTester($command);

        parent::setUp();
    }

    /** @test */
    public function shouldSolveKnownTest(): void
    {
        // answer we're trying to get is favor
        $this->commandTester->setInputs([
            'aapaa', // beast
            'acapa', // cairn
            'pcacc', // valor
            'pcacc', // vapor
            'ccccc', // favor
        ]);

        $this->commandTester->execute([]);

        $this->commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('Solved successfully: favor', $this->commandTester->getDisplay());
    }
}

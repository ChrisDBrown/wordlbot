<?php

declare(strict_types=1);

namespace App\Application\QueryHandler;

use App\Application\Query\WebSolverQuery;
use App\Domain\Services\Guesser;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Panther\Client;

use function count;
use function json_decode;
use function ltrim;
use function sleep;
use function sprintf;

use const PHP_EOL;

final class WebSolverQueryHandler implements MessageHandlerInterface
{
    private const WORDLE_URL          = 'https://www.powerlanguage.co.uk/wordle/';
    private const GUESS_RESULT_PULLER = "
			let rows = Array.from(document.querySelector('game-app').shadowRoot.querySelectorAll('game-row'));
			let tiles = Array.from(rows.map(row => row.shadowRoot.querySelectorAll('game-tile')));
			let pairs = Array.from(tiles[%d]).map(tile => ({ letter: tile._letter, state: tile._state }));
			
			return JSON.stringify(pairs);
		";
    private const STATE_ABSENT        = 'absent';
    private const STATE_PRESENT       = 'present';
    private const STATE_CORRECT       = 'correct';
    private const STATE_MAP           = [
        self::STATE_ABSENT => Result::CHAR_ABSENT,
        self::STATE_PRESENT => Result::CHAR_PRESENT,
        self::STATE_CORRECT => Result::CHAR_CORRECT,
    ];

    public function __construct(private Guesser $guesser)
    {
    }

    public function __invoke(WebSolverQuery $query): string
    {
        $client = Client::createChromeClient('drivers/chromedriver');

        $client->request('GET', self::WORDLE_URL);

        sleep(1); // startup animation
        $client->getMouse()->clickTo('body');
        sleep(1); // modal close

        $resultHistory = new ResultHistory();

        do {
            $guess   = $this->guesser->guess($resultHistory);
            $outcome = $this->makeGuess($client, $guess, count($resultHistory->getResults()));

            $resultHistory->addResult(new Result($guess, $outcome));
        } while ($resultHistory->isSolved() === false);

        sleep(3); // success animation, modal open

        $puzzleNumber = $this->extractPuzzleNumber($client);

        $client->takeScreenshot(sprintf('var/images/%s.png', $puzzleNumber));

        return $this->buildResultCode($resultHistory, $puzzleNumber);
    }

    private function makeGuess(Client $client, string $guess, int $guessCount): string
    {
        $keyboard = $client->getKeyboard();
        $keyboard->sendKeys($guess . WebDriverKeys::ENTER);

        sleep(2); // guess outcome animation

        $output = $client->executeScript(sprintf(self::GUESS_RESULT_PULLER, $guessCount));

        $results = json_decode($output, true);

        $outcome = '';

        foreach ($results as $result) {
            $outcome .= self::STATE_MAP[$result['state']];
        }

        return $outcome;
    }

    private function extractPuzzleNumber(Client $client): string
    {
        $client->getMouse()->clickTo('body');
        sleep(1); // close win modal

        $client->executeScript("document.querySelector('game-app').shadowRoot.querySelector('#settings-button').click()");

        sleep(2); // open settings page

        $puzzleNumber = $client->executeScript("return document.querySelector('game-app').shadowRoot.querySelector('game-settings').shadowRoot.querySelector('#puzzle-number').innerHTML");

        $client->executeScript("document.querySelector('game-app').shadowRoot.querySelector('game-page').shadowRoot.querySelector('game-icon[icon=close]').click()");

        sleep(1); // close settings page

        return ltrim($puzzleNumber, '#');
    }

    private function buildResultCode(ResultHistory $resultHistory, string $puzzleNumber): string
    {
        $header = sprintf('Wordle %s %s/6', $puzzleNumber, count($resultHistory->getResults()));

        return $header . PHP_EOL . PHP_EOL . $resultHistory->getResultGrid();
    }
}

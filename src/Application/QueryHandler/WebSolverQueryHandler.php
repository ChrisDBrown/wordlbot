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
use function sleep;
use function sprintf;

final class WebSolverQueryHandler implements MessageHandlerInterface
{
    private const WORDLE_URL      = 'https://www.powerlanguage.co.uk/wordle/';
    private const SCRIPT_TEMPLATE = "
			let rows = Array.from(document.querySelector('game-app').shadowRoot.querySelectorAll('game-row'));
			let tiles = Array.from(rows.map(row => row.shadowRoot.querySelectorAll('game-tile')));
			let pairs = Array.from(tiles[%d]).map(tile => ({ letter: tile._letter, state: tile._state }));
			
			return JSON.stringify(pairs);
		";
    private const STATE_ABSENT    = 'absent';
    private const STATE_PRESENT   = 'present';
    private const STATE_CORRECT   = 'correct';
    private const STATE_MAP       = [
        self::STATE_ABSENT => Result::CHAR_NO_MATCH,
        self::STATE_PRESENT => Result::CHAR_LETTER_MATCH,
        self::STATE_CORRECT => Result::CHAR_POSITION_MATCH,
    ];

    public function __construct(private Guesser $guesser)
    {
    }

    public function __invoke(WebSolverQuery $query): void
    {
        $client = Client::createChromeClient('drivers/chromedriver');

        $client->request('GET', self::WORDLE_URL);

        sleep(1);
        $client->getMouse()->clickTo('body');
        sleep(1);

        $resultHistory = new ResultHistory();

        do {
            $guess   = $this->guesser->guess($resultHistory);
            $outcome = $this->makeGuess($client, $guess, count($resultHistory->getResults()));

            $resultHistory->addResult(new Result($guess, $outcome));
        } while ($outcome !== 'ppppp');

        $client->takeScreenshot('test.png');
    }

    private function makeGuess(Client $client, string $guess, int $guessCount): string
    {
        $keyboard = $client->getKeyboard();
        $keyboard->sendKeys($guess . WebDriverKeys::ENTER);

        sleep(2);

        $script = sprintf(self::SCRIPT_TEMPLATE, $guessCount);
        $output = $client->executeScript($script);

        $results = json_decode($output, true);

        $outcome = '';

        foreach ($results as $result) {
            $outcome .= self::STATE_MAP[$result['state']];
        }

        return $outcome;
    }
}

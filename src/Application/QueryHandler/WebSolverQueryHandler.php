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
use function explode;
use function json_decode;
use function sleep;
use function sprintf;
use function str_replace;

use const PHP_EOL;

final class WebSolverQueryHandler implements MessageHandlerInterface
{
    private const WORDLE_URL               = 'https://www.powerlanguage.co.uk/wordle/';
    private const GUESS_RESULT_PULLER      = "
			let rows = Array.from(document.querySelector('game-app').shadowRoot.querySelectorAll('game-row'));
			let tiles = Array.from(rows.map(row => row.shadowRoot.querySelectorAll('game-tile')));
			let pairs = Array.from(tiles[%d]).map(tile => ({ letter: tile._letter, state: tile._state }));
			
			return JSON.stringify(pairs);
		";
    private const SHARE_CODE_TO_TEXT_BOX   = "
            document.querySelector('game-app').shadowRoot.querySelector('game-stats').shadowRoot.querySelector('#share-button').click();
            
            let input = document.createElement('input');
			input.setAttribute('type', 'textarea');
			input.setAttribute('id', 'paste-box');
			
			document.querySelector('body').appendChild(input);
			
			document.querySelector('#paste-box').focus();
        ";
    private const SHARE_CODE_FROM_TEXT_BOX = "
            let pasteBox = document.querySelector('#paste-box')
            const shareCode = pasteBox.value;
            pasteBox.remove();
            
            return shareCode;
        ";
    private const STATE_ABSENT             = 'absent';
    private const STATE_PRESENT            = 'present';
    private const STATE_CORRECT            = 'correct';
    private const STATE_MAP                = [
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

        $shareCode = $this->extractShareCode($client);

        [$header, $body] = explode('  ', $shareCode);

        $client->getMouse()->clickTo('body');
        sleep(1); // modal close

        $client->takeScreenshot(explode(' ', $header)[1] . '.png');

        return $header . PHP_EOL . PHP_EOL . str_replace(' ', PHP_EOL, $body);
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

    private function extractShareCode(Client $client): string
    {
        $client->executeScript(self::SHARE_CODE_TO_TEXT_BOX);

        $keyboard = $client->getKeyboard();
        $keyboard->pressKey(WebDriverKeys::COMMAND);
        $keyboard->sendKeys('v');
        $keyboard->releaseKey(WebDriverKeys::COMMAND);

        return $client->executeScript(self::SHARE_CODE_FROM_TEXT_BOX);
    }
}

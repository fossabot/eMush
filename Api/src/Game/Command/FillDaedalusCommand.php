<?php

namespace Mush\Game\Command;

use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\CharacterConfigRepository;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'mush:fill-daedalus',
    description: 'Fill a new Daedalus',
    hidden: false
)]
class FillDaedalusCommand extends Command
{
    private HttpClientInterface $httpClient;
    private CharacterConfigRepository $characterConfigRepository;
    private string $identityServerUri;
    private string $eMushApiUri;

    private const OPTION_NUMBER = 'number';
    private const OPTION_CHAO_FINOLA = 'chao_finola';
    private const OPTION_ANDIE_DEREK = 'andie_derek';
    private const OPTION_DAEDALUS_ID = 'daedalus_id';

    public function __construct(HttpClientInterface $httpClient, CharacterConfigRepository $characterConfigRepository)
    {
        parent::__construct();
        $this->httpClient = $httpClient;
        $this->characterConfigRepository = $characterConfigRepository;
        $this->identityServerUri = $_ENV['IDENTITY_SERVER_URI'];
        $this->eMushApiUri = $_ENV['EMUSH_BASE_URI'];
    }

    protected function configure(): void
    {
        $this->addOption($this::OPTION_NUMBER, null, InputOption::VALUE_OPTIONAL, 'Number of member to board ?', 16);
        $this->addOption($this::OPTION_CHAO_FINOLA, null, InputOption::VALUE_OPTIONAL, 'Accept Chao and Finola on board', false);
        $this->addOption($this::OPTION_ANDIE_DEREK, null, InputOption::VALUE_OPTIONAL, 'Accept Andie and Derek on board ?', false);
        $this->addOption($this::OPTION_DAEDALUS_ID, null, InputOption::VALUE_OPTIONAL, 'Daedalus id ?', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $daedalusId = $input->getOption($this::OPTION_DAEDALUS_ID);
        $numberOfMemberToBoard = $input->getOption($this::OPTION_NUMBER);
        if ($numberOfMemberToBoard < 1 || $numberOfMemberToBoard > 16) {
            $io->error($this::OPTION_NUMBER . ' should be between 1 and 16');

            return Command::INVALID;
        }
        $io->info("$numberOfMemberToBoard character will be added to daedalus $daedalusId");

        $isChaoAndFinola = $input->getOption($this::OPTION_CHAO_FINOLA) === null;
        $isAndieAndDerek = $input->getOption($this::OPTION_ANDIE_DEREK) === null;
        if ($isAndieAndDerek && $isChaoAndFinola) {
            $io->error($this::OPTION_CHAO_FINOLA . ' and ' . $this::OPTION_ANDIE_DEREK . ' are mutually exclusive');

            return Command::INVALID;
        }

        if ($isChaoAndFinola) {
            $io->info('Andie and Derek wont be added to daedalus');
        }
        if ($isAndieAndDerek) {
            $io->info('Chao and Finola wont be added to daedalus');
        }

        $io->title('Filling Daedalus...');

        /** @var CharacterConfig[] $allCharacter */
        $allCharacter = $this->characterConfigRepository->findAll();

        $count = 0;
        foreach ($allCharacter as $character) {
            $name = $character->getName();

            $io->info($name . ' on boarding ...');

            if ($isAndieAndDerek && $this->isChaoOrFinola($name)) {
                $io->info("$name not allowed on daedalus, skipping ...");
                continue;
            }
            if ($isChaoAndFinola && $this->isAndieOrDerek($name)) {
                $io->info("$name not allowed on daedalus, skipping ...");
                continue;
            }

            try {
                $loginFailed = false;

                $tryToLoginRequest = $this->httpClient->request(
                    'PUT',
                    "$this->identityServerUri/api/v1/auth/self",
                    ['json' => ['login' => $name, 'password' => '31323334353637383931']]
                );
                $result = [];
                parse_str($tryToLoginRequest->getHeaders()['set-cookie'][0], $result);
                /** @var string $allCharacter */
                $sid = explode(';', $result['sid'])[0];

                $client = HttpClient::create([
                    'headers' => [
                        'Cookie' => new Cookie('sid', $sid),
                    ],
                ]);
                $getTokenETResponse = $client->request(
                    'GET',
                    "$this->identityServerUri/oauth/authorize?access_type=offline&response_type=code&redirect_uri=http://localhost:8080/oauth/callback&client_id=emush@clients&scope=base&state=http://localhost:8081/token",
                    ['max_redirects' => 0]
                );
                $location = $getTokenETResponse->getHeaders(false)['location'];
                $queryResult = [];

                $url = parse_url($location[0]);
                if ($url == null) {
                    $io->warning("$name cannot join Daedalus : Cannot retrieve url or redirect from ET response for authorization token. Skipping ...");
                    continue;
                }
                $query = $url['query'] ?? null;
                if ($query == null) {
                    $io->warning("$name cannot join Daedalus. : Cannot retrieve query part from url from ET response for authorization token. Skipping ...");
                    continue;
                }
                parse_str($query, $queryResult);

                $tokenET = $queryResult['code'];
                $fistTokenApiResponse = $client->request(
                    'GET',
                    "$this->eMushApiUri/oauth/callback?code=$tokenET&state=http://localhost:8081/token",
                    ['max_redirects' => 0]
                );
                $location = $fistTokenApiResponse->getHeaders(false)['location'];
                if ($location[0] == null) {
                    $io->warning("$name cannot join Daedalus : Cannot retrieve url or redirect from eMush first response for authorization token. Skipping ...");
                    continue;
                }
                $url = parse_url($location[0]);
                if ($url == null) {
                    $io->warning("$name cannot join Daedalus. : Cannot parse url from eMush first response for authorization token. Skipping ...");
                    continue;
                }
                $query = $url['query'] ?? null;
                if ($query == null) {
                    $io->warning("$name cannot join Daedalus. : Cannot retrieve query part from url from eMush first response for authorization token. Skipping ...");
                    continue;
                }
                parse_str($query, $queryResult);

                $fistTokenApi = $queryResult['code'];
                $realTokenApiResponse = $client->request(
                    'POST',
                    "$this->eMushApiUri/oauth/token",
                    ['json' => ['grant_type' => 'authorization_code', 'code' => $fistTokenApi]]
                );
                $token = json_decode($realTokenApiResponse->getContent())->token;
                $userId = json_decode(base64_decode(explode('.', $token)[1]))->userId;

                $joinDaedalusResponse = $this->httpClient->request(
                    'POST',
                    "$this->eMushApiUri/api/v1/player",
                    [
                        'json' => ['user' => $userId, 'daedalus' => $daedalusId, 'character' => $name],
                        'headers' => ['Authorization' => "Bearer $token"],
                    ],
                );
                $statusCode = $joinDaedalusResponse->getStatusCode();
                if ($statusCode != 200) {
                    $body = $joinDaedalusResponse->getContent(false);
                    $io->warning("$name cannot join Daedalus. Error while joind daedalus : $body");
                }
                ++$count;
                $io->info($name . ' joined Daedalus !');
            } catch (\Exception $e) {
                $trace = $e->getTraceAsString();
                $message = $e->getMessage();
                $io->warning("$name cannot join Daedalus. Error while joind daedalus : $message -> $trace");
                continue;
            }

            if ($this->isAndieOrDerek($name)) {
                $isAndieAndDerek = true;
            }
            if ($this->isChaoOrFinola($name)) {
                $isChaoAndFinola = true;
            }

            if ($count == $numberOfMemberToBoard) {
                break;
            }
        }
        $io->info("$count member joined the Daedalus.");

        return Command::SUCCESS;
    }

    public function isAndieOrDerek(string $name): bool
    {
        return $name == 'andie' || $name == 'derek';
    }

    public function isChaoOrFinola(string $name): bool
    {
        return $name == 'chao' || $name == 'finola';
    }
}

<?php

namespace DataMat\GrinningCat\Tests\Mocks;

use DataMat\GrinningCat\Clients\WSClient;
use WebSocket\Client;

class TestWsClient extends WSClient
{
    private Client $mockClient;

    public function __construct(
        Client $mockClient,
        string $host = 'example.com',
        ?int $port = null,
        ?string $apikey = null,
        ?bool $isWSS = null,
    ) {
        $this->mockClient = $mockClient;
        parent::__construct($host, $port, $apikey, $isWSS);
    }

    protected function createWsClient(string $agentId, string $userId, ?string $chatId = null): Client
    {
        return $this->mockClient;
    }
}
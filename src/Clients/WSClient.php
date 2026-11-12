<?php

namespace DataMat\GrinningCat\Clients;

use Phrity\Net\Uri;
use WebSocket\Client;
use WebSocket\Middleware\CloseHandler;
use WebSocket\Middleware\PingResponder;

class WSClient
{
    protected ?Client $wsClient = null;
    protected string $host;
    protected ?int $port;
    protected ?string $apikey;
    protected ?string $token;
    protected bool $isWSS;

    public function __construct(
        string $host,
        ?int $port = null,
        ?string $apikey = null,
        ?bool $isWSS = null,
    ) {

        $this->host = $host;
        $this->port = $port;
        $this->apikey = $apikey;
        $this->token = null;
        $this->isWSS = $isWSS ?? false;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getClient(string $agentId, string $userId, ?string $chatId = null): Client
    {
        if (!$this->apikey && !$this->token) {
            throw new \InvalidArgumentException('You must provide an apikey or a token');
        }

        if (!$this->wsClient) {
            $this->wsClient = $this->createWsClient($agentId, $userId, $chatId);
        }

        return $this->wsClient;
    }

    public function close()
    {
        if ($this->wsClient) {
            $this->wsClient->disconnect();
            $this->wsClient->close();
            $this->wsClient = null;
        }
    }

    public function getWsUri(string $agentId, string $userId, ?string $chatId = null): Uri
    {
        $query = [];
        $query['user_id'] = $userId;

        $path = sprintf('ws/%s', $agentId);
        if ($chatId) {
            $path .= sprintf('/%s', $chatId);
        }

        return (new Uri())
            ->withScheme($this->isWSS ? 'wss' : 'ws')
            ->withHost($this->host)
            ->withPath($path)
            ->withQueryItems($query)
            ->withPort($this->port)
        ;
    }

    protected function createWsClient(string $agentId, string $userId, ?string $chatId = null): Client
    {
        $bearerToken = $this->token ?? $this->apikey;

        $client = new Client($this->getWsUri($agentId, $userId, $chatId), [
            'headers' => [
                'Authorization' => 'Bearer ' . $bearerToken
            ]
        ]);

        $client->setPersistent(true)
            ->setTimeout(100000)
            // Add CloseHandler middleware
            ->addMiddleware(new CloseHandler())
            // Add PingResponder middleware
            ->addMiddleware(new PingResponder());
        return $client;
    }
}
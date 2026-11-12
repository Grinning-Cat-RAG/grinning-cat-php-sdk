<?php

namespace DataMat\GrinningCat\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Phrity\Net\Uri;

class HttpClient
{
    protected Uri $httpUri;
    protected ?string $apikey;
    protected ?string $token;
    protected ?HandlerStack $handlerStack;

    public function __construct(
        string $host,
        ?int $port = null,
        ?string $apikey = null,
        ?bool $isHTTPs = false,
        ?HandlerStack $handlerStack = null,
    ) {
        $this->apikey = $apikey;
        $this->token = null;
        $this->handlerStack = $handlerStack;

        $this->httpUri = (new Uri())
            ->withHost($host)
            ->withPort($port)
            ->withScheme($isHTTPs ? 'https' : 'http');
    }

    /**
     * Builds a new Guzzle client carrying the Authorization header plus the given extra headers.
     *
     * @param array<string, string> $headers
     */
    public function createHttpClient(?HandlerStack $handlerStack = null, array $headers = []): Client
    {
        $config = [
            'base_uri' => $this->httpUri,
            'headers' => array_merge($this->baseHeaders(), $headers),
        ];

        $handlerStack = $handlerStack ?? $this->handlerStack;
        if ($handlerStack) {
            $config['handler'] = $handlerStack;
        }

        return new Client($config);
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Every call returns a new client whose headers are fixed at creation, so they can never leak into, or be
     * overwritten by, the requests of another client.
     */
    public function getClient(?string $agentId = null, ?string $userId = null, ?string $chatId = null): Client
    {
        if (!$this->apikey && !$this->token) {
            throw new \InvalidArgumentException('You must provide an apikey or a token');
        }

        $headers = [];
        if (!empty($agentId)) {
            $headers['X-Agent-ID'] = $agentId;
        }
        if (!empty($userId)) {
            $headers['X-User-ID'] = $userId;
        }
        if (!empty($chatId)) {
            $headers['X-Chat-ID'] = $chatId;
        }

        return $this->createHttpClient(null, $headers);
    }

    public function getHttpUri(): Uri
    {
        return $this->httpUri;
    }

    /**
     * @return array<string, string>
     */
    protected function baseHeaders(): array
    {
        $bearer = !empty($this->token) ? $this->token : $this->apikey;

        return empty($bearer) ? [] : ['Authorization' => 'Bearer ' . $bearer];
    }
}

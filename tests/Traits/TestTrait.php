<?php

namespace DataMat\GrinningCat\Tests\Traits;

use DataMat\GrinningCat\GrinningCatClient;
use DataMat\GrinningCat\Clients\HttpClient as BaseHttpClient;
use DataMat\GrinningCat\Tests\Mocks\TestHttpClient;
use DataMat\GrinningCat\Tests\Mocks\TestWsClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Message\StreamInterface;
use WebSocket\Client as WsClient;
use WebSocket\Message\Message as WebSocketMessage;

trait TestTrait
{
    protected string $apikey = 'apikey';
    protected string $token = 'token';

    /**
     * @throws \JsonException|Exception
     */
    protected function getGrinningCatClient(
        ?string $apikey = null,
        ?array $content = null,
        ?int $code = null
    ): GrinningCatClient {
        $httpClient = $this->getHttpClient($apikey, $content, $code);
        $wsClient = $this->getWsClient($apikey, $content);

        return new GrinningCatClient($wsClient, $httpClient);
    }

    /**
     * @throws \JsonException|Exception
     */
    protected function getHttpClient(
        string $apikey = null,
        ?array $content = null,
        ?int $code = null,
        ?string $host = null,
        ?int $port = null,
    ): TestHttpClient {
        $content = $content ?? [];
        $code = $code ?? 200;
        $host = $host ?? 'example.com';
        $port = $port ?? 8080;

        return new TestHttpClient($this->getHttpClientMockGuzzle($content, $code), $host, $port, $apikey);
    }

    /**
     * @throws \JsonException|Exception
     */
    protected function getWsClient(
        ?string $apikey = null,
        ?array $content = null,
        ?string $host = null,
        ?int $port = null,
    ): TestWsClient{
        $content = $content ?? [];
        $host = $host ?? 'example.com';
        $port = $port ?? 8080;

        return new TestWsClient($this->getWebSocketClientMock($content), $host, $port, $apikey);
    }

    /**
     * @throws Exception|\JsonException
     */
    protected function getHttpClientMockGuzzle(?array $content = [], ?int $code = 200): HttpClient
    {
        // Convert array to JSON string if an array is provided
        $responseContent = json_encode($content, JSON_THROW_ON_ERROR);

        // Handle JSON encoding errors
        if (is_array($content) && json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Failed to encode content array as JSON: ' . json_last_error_msg());
        }

        // Create a mock PSR-7 Stream with the provided content
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($responseContent);
        $stream->method('__toString')->willReturn($responseContent);

        // Create a mock Response with the stream
        $response = $this->createMock(Response::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($code);

        // Create the HTTP client mock
        $httpClientMock = $this->createMock(HttpClient::class);

        // Configure all HTTP methods to return the response
        $methods = ['get', 'post', 'put', 'delete'];
        foreach ($methods as $method) {
            $httpClientMock
                ->method($method)
                ->willReturnCallback(function ($uri, array $options = []) use ($response) {
                    return $response;
                });
        }

        return $httpClientMock;
    }

    /**
     * @throws Exception|\JsonException
     */
    protected function getWebSocketClientMock(?array $content = []): WsClient
    {
        // Convert array to JSON string if an array is provided
        $responseContent = json_encode($content, JSON_THROW_ON_ERROR);

        // Create WebSocket Message stub
        $wsMessage = $this->createStub(WebSocketMessage::class);
        $wsMessage->method('getPayload')->willReturn($responseContent);
        $wsMessage->method('getContent')->willReturn($responseContent);
        $wsMessage->method('__toString')->willReturn($responseContent);

        $wsClientMock = $this->createMock(WsClient::class);
        $wsClientMock->method('receive')->willReturn($wsMessage);

        return $wsClientMock;
    }

    /**
     * Real HttpClient whose requests are recorded in $container and answered with the given bodies, in order.
     *
     * @param array<int, array<string, mixed>>|null $bodies
     * @param array<int, array<string, mixed>> $container
     */
    protected function getMockedGuzzleClientWithHandlerStack(array &$container, ?array $bodies = null): BaseHttpClient
    {
        $responses = array_map(
            static fn (array $body) => new Response(200, [], json_encode($body, JSON_THROW_ON_ERROR)),
            $bodies ?? [[], [], []]
        );

        $handlerStack = HandlerStack::create(new MockHandler($responses));
        $handlerStack->push(Middleware::history($container));

        return new BaseHttpClient('example.com', null, $this->apikey, false, $handlerStack);
    }
}

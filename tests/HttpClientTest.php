<?php

namespace DataMat\GrinningCat\Tests;

use DataMat\GrinningCat\Tests\Traits\TestTrait;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class HttpClientTest extends TestCase
{
    use TestTrait;

    /**
     * @throws GuzzleException
     */
    public function testSecureHeaders(): void
    {
        $container = [];

        $client = $this->getMockedGuzzleClientWithHandlerStack($container);
        $client->getClient('agent', 'user')->get('/test');

        self::assertCount(1, $container);

        /** @var RequestInterface $request */
        $request = $container[0]['request'];

        self::assertEquals('Bearer ' . $this->apikey, $request->getHeaderLine('Authorization'));
        self::assertEquals('user', $request->getHeaderLine('X-User-ID'));
        self::assertEquals('agent', $request->getHeaderLine('X-Agent-ID'));
        self::assertFalse($request->hasHeader('X-Chat-ID'));
    }

    /**
     * @throws GuzzleException
     */
    public function testJwtHeadersTakePrecedenceOverApikey(): void
    {
        $container = [];

        $client = $this->getMockedGuzzleClientWithHandlerStack($container);
        $client->setToken($this->token);
        $client->getClient('agent', 'user', 'chat')->get('/test');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];

        self::assertEquals('Bearer ' . $this->token, $request->getHeaderLine('Authorization'));
        self::assertEquals('agent', $request->getHeaderLine('X-Agent-ID'));
        self::assertEquals('user', $request->getHeaderLine('X-User-ID'));
        self::assertEquals('chat', $request->getHeaderLine('X-Chat-ID'));
    }

    /**
     * @throws GuzzleException
     */
    public function testHeadersBelongToTheClientThatWasRequested(): void
    {
        $container = [];

        $client = $this->getMockedGuzzleClientWithHandlerStack($container);
        $first = $client->getClient('agent1', 'user1', 'chat1');
        $second = $client->getClient('agent2');

        $first->get('/first');
        $second->get('/second');

        /** @var RequestInterface $firstRequest */
        $firstRequest = $container[0]['request'];
        /** @var RequestInterface $secondRequest */
        $secondRequest = $container[1]['request'];

        self::assertEquals('agent1', $firstRequest->getHeaderLine('X-Agent-ID'));
        self::assertEquals('user1', $firstRequest->getHeaderLine('X-User-ID'));
        self::assertEquals('chat1', $firstRequest->getHeaderLine('X-Chat-ID'));

        self::assertEquals('agent2', $secondRequest->getHeaderLine('X-Agent-ID'));
        self::assertFalse($secondRequest->hasHeader('X-User-ID'));
        self::assertFalse($secondRequest->hasHeader('X-Chat-ID'));
    }

    /**
     * @throws GuzzleException
     */
    public function testCreateHttpClientKeepsOnlyAuthorization(): void
    {
        $container = [];

        $client = $this->getMockedGuzzleClientWithHandlerStack($container);
        $client->getClient('agent', 'user', 'chat');
        $client->createHttpClient()->get('/test');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];

        self::assertEquals('Bearer ' . $this->apikey, $request->getHeaderLine('Authorization'));
        self::assertFalse($request->hasHeader('X-Agent-ID'));
        self::assertFalse($request->hasHeader('X-User-ID'));
        self::assertFalse($request->hasHeader('X-Chat-ID'));
    }

    /**
     * @throws GuzzleException
     */
    public function testCreateHttpClientWithoutCredentialsSendsNoAuthorization(): void
    {
        $container = [];

        $client = $this->getMockedGuzzleClientWithHandlerStack($container);
        $reflection = new \ReflectionClass($client);
        $reflection->getProperty('apikey')->setValue($client, null);

        $client->createHttpClient()->get('/auth/token');

        /** @var RequestInterface $request */
        $request = $container[0]['request'];

        self::assertFalse($request->hasHeader('Authorization'));
    }

    public function testCreateHttpClientWithoutHandlerStackKeepsDefaultHandlerAndAuthorization(): void
    {
        $client = new \DataMat\GrinningCat\Clients\HttpClient('example.com', null, $this->apikey);

        $guzzle = $client->createHttpClient();

        self::assertEquals('Bearer ' . $this->apikey, $guzzle->getConfig('headers')['Authorization']);
        self::assertNotNull($guzzle->getConfig('handler'));
    }
}

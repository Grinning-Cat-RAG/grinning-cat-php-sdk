<?php

namespace DataMat\GrinningCat\Tests;

use DataMat\GrinningCat\DTO\Api\Conversation\ConversationAttributesChangeOutput;
use DataMat\GrinningCat\GrinningCatClient;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Message\RequestInterface;

class ConversationEndpointTest extends BaseTest
{

    /**
     * @throws GuzzleException|Exception|\JsonException
     */
    public function testGetConversationHistorySuccess(): void
    {
        $expected = [
            'history' => [
                [
                    'who' => 'user',
                    'when' => 0.0,
                    'content' => [
                        'text' => 'Hey you!',
                    ],
                ],
                [
                    'who' => 'assistant',
                    'when' => 0.1,
                    'content' => [
                        'text' => 'Hi!',
                    ],
                ],
            ],
        ];

        $grinningCatClient = $this->getGrinningCatClient($this->apikey, $expected);

        $endpoint = $grinningCatClient->conversation();
        $result = $endpoint->getConversationHistory('agent', 'user', 'chat');

        self::assertEquals($expected, $result->toArray());
    }

    /**
     * @throws GuzzleException|Exception|\JsonException
     */
    public function testDeleteConversationSuccess(): void
    {
        $expected = ['deleted' => true];

        $grinningCatClient = $this->getGrinningCatClient($this->apikey, $expected);

        $endpoint = $grinningCatClient->conversation();
        $result = $endpoint->deleteConversation('agent', 'user', 'chat');

        self::assertEquals($expected['deleted'], $result->deleted);
    }

    /**
     * @param array<string, mixed> $body
     * @param array<int, array<string, mixed>> $container
     *
     * @throws \JsonException|Exception
     */
    private function getRecordingClient(array $body, array &$container): GrinningCatClient
    {
        $httpClient = $this->getMockedGuzzleClientWithHandlerStack($container, [$body]);

        return new GrinningCatClient($this->getWsClient($this->apikey), $httpClient);
    }

    /**
     * @param array<int, array<string, mixed>> $container
     */
    private function lastRequest(array $container): RequestInterface
    {
        self::assertCount(1, $container);

        return $container[0]['request'];
    }

    /**
     * @throws GuzzleException|Exception|\JsonException
     */
    public function testGetConversationHistorySendsChatIdHeader(): void
    {
        $container = [];
        $client = $this->getRecordingClient(['history' => []], $container);

        $client->conversation()->getConversationHistory('agent', 'user', 'chat1');

        $request = $this->lastRequest($container);
        self::assertEquals('chat1', $request->getHeaderLine('X-Chat-ID'));
        self::assertEquals('agent', $request->getHeaderLine('X-Agent-ID'));
        self::assertEquals('user', $request->getHeaderLine('X-User-ID'));
    }

    /**
     * @throws GuzzleException|Exception|\JsonException
     */
    public function testGetConversationSendsChatIdHeader(): void
    {
        $container = [];
        $client = $this->getRecordingClient([
            'chat_id' => 'chat1',
            'name' => 'n',
            'num_messages' => 0,
            'created_at' => 0.0,
            'updated_at' => 0.0,
        ], $container);

        $client->conversation()->getConversation('agent', 'user', 'chat1');

        self::assertEquals('chat1', $this->lastRequest($container)->getHeaderLine('X-Chat-ID'));
    }

    /**
     * @throws GuzzleException|Exception|\JsonException
     */
    public function testDeleteConversationSendsChatIdHeader(): void
    {
        $container = [];
        $client = $this->getRecordingClient(['deleted' => true], $container);

        $client->conversation()->deleteConversation('agent', 'user', 'chat1');

        self::assertEquals('chat1', $this->lastRequest($container)->getHeaderLine('X-Chat-ID'));
    }

    /**
     * @throws GuzzleException|Exception|\JsonException
     */
    public function testPutConversationAttributesSendsChatIdHeaderAndReturnsChangeOutput(): void
    {
        $container = [];
        $client = $this->getRecordingClient(['changed' => true], $container);

        $result = $client->conversation()->putConversationAttributes('agent', 'user', 'chat1', name: 'new');

        self::assertEquals('chat1', $this->lastRequest($container)->getHeaderLine('X-Chat-ID'));
        self::assertInstanceOf(ConversationAttributesChangeOutput::class, $result);
        self::assertTrue($result->changed);
    }

    /**
     * @throws GuzzleException|Exception|\JsonException
     */
    public function testPutConversationAttributesSendsMetadataAndName(): void
    {
        $container = [];
        $client = $this->getRecordingClient(['changed' => true], $container);

        $client->conversation()->putConversationAttributes('agent', 'user', 'chat1', 'new', ['a' => 1]);

        $request = $this->lastRequest($container);
        self::assertEquals(['name' => 'new', 'metadata' => ['a' => 1]], json_decode((string) $request->getBody(), true));
    }

    /**
     * @throws GuzzleException|Exception|\JsonException
     */
    public function testPutConversationAttributesRequiresNameOrMetadata(): void
    {
        $container = [];
        $client = $this->getRecordingClient(['changed' => true], $container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Either name or metadata must be provided');

        $client->conversation()->putConversationAttributes('agent', 'user', 'chat1');
    }
}

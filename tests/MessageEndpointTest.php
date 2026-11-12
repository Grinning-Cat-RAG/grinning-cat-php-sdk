<?php

namespace DataMat\GrinningCat\Tests;

use DataMat\GrinningCat\DTO\Api\Message\ChatOutput;
use DataMat\GrinningCat\DTO\Message;
use DataMat\GrinningCat\Tests\Traits\TestTrait;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MessageEndpointTest extends TestCase
{
    use TestTrait;

    /**
     * @throws \JsonException|GuzzleException|Exception
     */
    public function testSendHttpMessage(): void
    {
        $expected = $this->chatOutput();

        $grinningCatClient = $this->getGrinningCatClient($this->apikey, $expected);

        $endpoint = $grinningCatClient->message();
        $response = $endpoint->sendHttpMessage(
            new Message($expected['message']['text']), 'agent_id', 'user_id'
        );

        self::assertInstanceOf(ChatOutput::class, $response);

        self::assertEquals($response->message->text, $expected['message']['text']);
        self::assertEquals($response->message->type, $expected['message']['type']);
        self::assertEquals($response->message->why->input, $expected['message']['why']['input']);
        self::assertEquals($expected['message']['why']['memory'], $response->message->why->memory);
    }

    /**
     * @throws \JsonException|GuzzleException|Exception
     */
    public function testSendWebsocketMessage(): void
    {
        $expected = $this->chatOutput();

        $grinningCatClient = $this->getGrinningCatClient(
            $this->apikey,
            ['type' => 'chat', 'content' => json_encode($expected, JSON_THROW_ON_ERROR)]
        );

        $endpoint = $grinningCatClient->message();
        $response = $endpoint->sendWebsocketMessage(
            new Message($expected['message']['text']), 'agent_id', 'user_id'
        );

        self::assertInstanceOf(ChatOutput::class, $response);

        self::assertEquals($response->message->text, $expected['message']['text']);
        self::assertEquals($response->message->type, $expected['message']['type']);
        self::assertEquals($response->message->why->input, $expected['message']['why']['input']);
        self::assertEquals($expected['message']['why']['memory'], $response->message->why->memory);
    }

    /**
     * @return array<string, mixed>
     */
    private function chatOutput(): array
    {
        return [
            'agent_id' => 'agent_id',
            'user_id' => 'user_id',
            'chat_id' => 'chat_id',
            'message' => [
                'text' => 'Hello World',
                'type' => 'chat',
                'why' => [
                    'input' => 'input',
                    'memory' => [],
                ],
            ],
        ];
    }
}

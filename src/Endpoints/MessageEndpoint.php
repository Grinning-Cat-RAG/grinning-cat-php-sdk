<?php

namespace DataMat\GrinningCat\Endpoints;

use DataMat\GrinningCat\DTO\Api\Message\ChatOutput;
use DataMat\GrinningCat\DTO\Message;
use Closure;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class MessageEndpoint extends AbstractEndpoint
{
    /**
     * This endpoint sends a message to the agent identified by the agentId parameter. The message is sent via HTTP.
     *
     * @throws GuzzleException|\Exception
     */
    public function sendHttpMessage(
        Message $message,
        string $agentId,
        string $userId,
        ?string $chatId = null,
    ): ChatOutput {
        return $this->postJson(
            '/message',
            $agentId,
            ChatOutput::class,
            $message->toArray(),
            $userId,
            $chatId,
        );
    }

    /**
     * This endpoint sends a message to the agent identified by the agentId parameter. The message is sent via WebSocket.
     *
     * @throws \JsonException|Exception
     */
    public function sendWebsocketMessage(
        Message $message,
        string $agentId,
        string $userId,
        ?string $chatId = null,
        ?Closure $closure = null
    ): ChatOutput {
        $json = json_encode($message->toArray(), JSON_THROW_ON_ERROR);
        $client = $this->getWsClient($agentId, $userId, $chatId);

        try {
            $client->text($json);

            while (true) {
                $response = $client->receive();
                $content = $response->getContent();

                if ($content === 'ping') {
                    $client->text('pong');
                    continue;
                }
                if ($content === 'pong') {
                    continue;
                }

                $decoded = json_decode($content, true);

                if (($decoded['type'] ?? null) !== 'chat') {
                    $closure?->call($this, $decoded);
                    continue;
                }

                return $this->deserialize($decoded['content'], ChatOutput::class);
            }
        } catch (Exception $ex) {
            $this->client->getWsClient()->close();
            throw new RuntimeException('WebSocket error: ' . $ex->getMessage(), 0, $ex);
        }
    }
}
<?php

namespace DataMat\GrinningCat\Endpoints;

use DataMat\GrinningCat\DTO\Api\Conversation\ConversationDeleteOutput;
use DataMat\GrinningCat\DTO\Api\Conversation\ConversationHistoryOutput;
use DataMat\GrinningCat\DTO\Api\Conversation\ConversationAttributesChangeOutput;
use DataMat\GrinningCat\DTO\Api\Conversation\ConversationsResponse;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class ConversationEndpoint extends AbstractEndpoint
{
    protected string $prefix = '/conversations';

    /**
     * This endpoint returns the conversation history.
     *
     * @throws GuzzleException|\JsonException|RuntimeException
     */
    public function getConversationHistory(string $agentId, string $userId, string $chatId): ConversationHistoryOutput
    {
        return $this->get(
            $this->formatUrl($chatId . '/history'),
            $agentId,
            ConversationHistoryOutput::class,
            $userId,
            chatId: $chatId,
        );
    }

    /**
     * This endpoint returns all conversation attributes for a given agent and user.
     *
     * @return ConversationsResponse[]
     * @throws \JsonException
     */
    public function getConversations(string $agentId, string $userId): array
    {
        $response = $this->getHttpClient($agentId, $userId)->get($this->prefix);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                sprintf('Failed to fetch data from endpoint %s: %s', $this->prefix, $response->getReasonPhrase())
            );
        }

        $response = $this->client->getSerializer()->decode($response->getBody()->getContents(), 'json');
        $result = [];
        foreach ($response as $item) {
            $result[] = $this->deserialize(
                json_encode($item, JSON_THROW_ON_ERROR),
                ConversationsResponse::class,
                'json'
            );
        }
        return $result;
    }

    /**
     * This endpoint returns the attributes of a given conversation, for a given agent and user.
     *
     * @throws GuzzleException|\JsonException|RuntimeException
     */
    public function getConversation(string $agentId, string $userId, string $chatId): ConversationsResponse
    {
        return $this->get(
            $this->formatUrl($chatId),
            $agentId,
            ConversationsResponse::class,
            $userId,
            chatId: $chatId,
        );
    }

    /**
     * This endpoint deletes the conversation.
     *
     * @throws GuzzleException
     */
    public function deleteConversation(
        string $agentId,
        string $userId,
        string $chatId,
    ): ConversationDeleteOutput {
        return $this->delete(
            $this->formatUrl($chatId),
            $agentId,
            ConversationDeleteOutput::class,
            $userId,
            chatId: $chatId,
        );
    }

    /**
     * This endpoint changes the attributes of the conversation.
     *
     * @param array<string, mixed>|null $metadata
     *
     * @throws GuzzleException
     */
    public function putConversationAttributes(
        string $agentId,
        string $userId,
        string $chatId,
        ?string $name = null,
        ?array $metadata = null,
    ): ConversationAttributesChangeOutput {
        if (!$name && !$metadata) {
            throw new RuntimeException('Either name or metadata must be provided');
        }

        $payload = [];
        if ($name) {
            $payload['name'] = $name;
        }
        if ($metadata) {
            $payload['metadata'] = $metadata;
        }

        return $this->put(
            $this->formatUrl($chatId),
            $agentId,
            ConversationAttributesChangeOutput::class,
            $payload,
            $userId,
            $chatId,
        );
    }
}
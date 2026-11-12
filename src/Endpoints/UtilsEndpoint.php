<?php

namespace DataMat\GrinningCat\Endpoints;

use DataMat\GrinningCat\DTO\Api\Admins\AgentClonedOutput;
use DataMat\GrinningCat\DTO\Api\Admins\AgentCreatedOutput;
use DataMat\GrinningCat\DTO\Api\Admins\AgentOutput;
use DataMat\GrinningCat\DTO\Api\Admins\AgentUpdatedOutput;
use DataMat\GrinningCat\DTO\Api\Admins\ResetOutput;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;

class UtilsEndpoint extends AbstractEndpoint
{
    protected string $prefix = '/utils';

    /**
     * This endpoint is used to reset the system to factory settings. This will delete all data in the system.
     *
     * @throws GuzzleException
     */
    public function postFactoryReset(): ResetOutput
    {
        return $this->postJson(
            $this->formatUrl('/factory/reset/'),
            $this->systemId,
            ResetOutput::class,
        );
    }

    /**
     * This endpoint is used to retrieve all the agents in the system.
     *
     * @return AgentOutput[]
     *
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getAgents(): array
    {
        $response = $this->getHttpClient($this->systemId)->get($this->formatUrl('/agents/'));
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
                AgentOutput::class,
                'json'
            );
        }
        return $result;
    }

    /**
     * This endpoint is used to create a new agent from scratch.
     *
     * @throws GuzzleException
     */
    public function postAgentCreate(string $agentId, ?array $metadata = null): AgentCreatedOutput
    {
        $endpoint = $this->formatUrl('/agents/create/');
        $payload = ['agent_id' => $agentId];
        if ($metadata) {
            $payload['metadata'] = $metadata;
        }

        return $this->postJson($endpoint, $this->systemId, AgentCreatedOutput::class, $payload);
    }

    /**
     * This endpoint is used to reset the agent to factory settings. This will delete all data in the agent.
     *
     * @throws GuzzleException
     */
    public function postAgentReset(string $agentId): ResetOutput
    {
        return $this->postJson(
            $this->formatUrl('/agents/reset/'),
            $agentId,
            ResetOutput::class,
        );
    }

    /**
     * This endpoint is used to reset the agent to factory settings. This will delete all data in the agent.
     *
     * @throws GuzzleException
     */
    public function postAgentDestroy(string $agentId): ResetOutput
    {
        return $this->postJson(
            $this->formatUrl('/agents/destroy/'),
            $agentId,
            ResetOutput::class,
        );
    }

    /**
     * This endpoint is used to clone an existing agent to a new one.
     *
     * @throws GuzzleException
     */
    public function postAgentClone(string $agentId, string $newAgentId): AgentClonedOutput
    {
        return $this->postJson(
            $this->formatUrl('/agents/clone/'),
            $agentId,
            AgentClonedOutput::class,
            ['agent_id' => $newAgentId],
        );
    }

    /**
     * Updates information for an existing agent with the given metadata.
     *
     * @param array<string, mixed> $metadata
     */
    public function putAgent(string $agentId, array $metadata): AgentUpdatedOutput
    {
        return $this->put(
            $this->formatUrl('/agents/'),
            $agentId,
            AgentUpdatedOutput::class,
            ["metadata" => $metadata],
        );
    }
}
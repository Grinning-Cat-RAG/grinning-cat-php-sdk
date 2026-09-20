<?php

namespace DataMat\GrinningCat\Endpoints;

use DataMat\GrinningCat\DTO\Api\Factory\FactoryObjectSettingOutput;
use DataMat\GrinningCat\DTO\Api\Factory\FactoryObjectSettingsOutput;
use GuzzleHttp\Exception\GuzzleException;

class IngestionEndpoint extends AbstractEndpoint
{
    protected string $prefix = '/ingestion';

    /**
     * This endpoint returns the settings of all ingestions. Ingestions are set to a system level, so usable by all
     * the agents in the system.
     *
     * @throws GuzzleException
     */
    public function getIngestionsSettings(): FactoryObjectSettingsOutput
    {
        return $this->get(
            $this->formatUrl('/settings'),
            $this->systemId,
            FactoryObjectSettingsOutput::class,
        );
    }

    /**
     * This endpoint returns the settings of a specific ingestion. Ingestions are set to a system level, so usable by all
     * the agents in the system.
     *
     * @throws GuzzleException
     */
    public function getIngestionSettings(string $ingestion): FactoryObjectSettingOutput
    {
        return $this->get(
            $this->formatUrl('/settings/' . $ingestion),
            $this->systemId,
            FactoryObjectSettingOutput::class,
        );
    }

    /**
     * This endpoint updates the settings of a specific ingestion. Ingestions are set to a system level, so usable by all
     * the agents in the system.
     *
     * @param array<string, mixed> $values
     *
     * @throws GuzzleException
     */
    public function putIngestionSettings(string $ingestion, array $values): FactoryObjectSettingOutput
    {
        return $this->put(
            $this->formatUrl('/settings/' . $ingestion),
            $this->systemId,
            FactoryObjectSettingOutput::class,
            $values,
        );
    }
}
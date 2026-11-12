<?php

namespace DataMat\GrinningCat\Endpoints;

use DataMat\GrinningCat\DTO\Api\Factory\FactoryObjectSettingOutput;
use DataMat\GrinningCat\DTO\Api\Factory\FactoryObjectSettingsOutput;
use DataMat\GrinningCat\DTO\Api\FileManager\FileManagerAttributes;
use DataMat\GrinningCat\DTO\Api\FileManager\FileManagerDeletedFiles;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;

class FileManagerEndpoint extends AbstractEndpoint
{
    protected string $prefix = '/file_manager';

    /**
     * This endpoint returns the settings of all plugin file managers.
     *
     * @throws GuzzleException
     */
    public function getFileManagersSettings(string $agentId): FactoryObjectSettingsOutput
    {
        return $this->get(
            $this->formatUrl('/settings'),
            $agentId,
            FactoryObjectSettingsOutput::class,
        );
    }

    /**
     * This endpoint returns the settings of a specific plugin file manager.
     *
     * @throws GuzzleException
     */
    public function getFileManagerSettings(string $fileManager, string $agentId): FactoryObjectSettingOutput
    {
        return $this->get(
            $this->formatUrl('/settings/' . $fileManager),
            $agentId,
            FactoryObjectSettingOutput::class,
        );
    }

    /**
     * This endpoint updates the settings of a specific Plugin file manager.
     *
     * @param array<string, mixed> $values
     *
     * @throws GuzzleException
     */
    public function putFileManagerSettings(
        string $fileManager,
        string $agentId,
        array $values,
    ): FactoryObjectSettingOutput {
        return $this->put(
            $this->formatUrl('/settings/' . $fileManager),
            $agentId,
            FactoryObjectSettingOutput::class,
            $values,
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getFileManagerAttributes(string $agentId, ?string $chatId = null): FileManagerAttributes
    {
        return $this->get($this->prefix, $agentId, FileManagerAttributes::class, null, null, $chatId);
    }

    public function getFile(string $agentId, string $fileName, ?string $chatId = null): StreamInterface
    {
        return $this->getHttpClient($agentId, null, $chatId)->get($this->formatUrl('/files/' . $fileName), [
            'stream' => true
        ])->getBody();
    }

    public function deleteFile(string $agentId, string $fileName, ?string $chatId = null): FileManagerDeletedFiles
    {
        return $this->delete(
            $this->formatUrl('/files/' . $fileName),
            $agentId,
            FileManagerDeletedFiles::class,
            null,
            null,
            $chatId,
        );
    }

    public function deleteFiles(string $agentId, ?string $chatId = null): FileManagerDeletedFiles
    {
        return $this->delete(
            $this->formatUrl('/files'),
            $agentId,
            FileManagerDeletedFiles::class,
            null,
            null,
            $chatId,
        );
    }
}
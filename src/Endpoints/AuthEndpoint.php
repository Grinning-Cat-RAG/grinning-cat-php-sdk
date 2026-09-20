<?php

namespace DataMat\GrinningCat\Endpoints;

use DataMat\GrinningCat\DTO\Api\AgentMatchOutput;
use DataMat\GrinningCat\DTO\Api\MeOutput;
use DataMat\GrinningCat\DTO\Api\TokenOutput;
use DataMat\GrinningCat\DTO\Api\UserMeOutput;
use GuzzleHttp\Exception\GuzzleException;

class AuthEndpoint extends AbstractEndpoint
{
    protected string $prefix = '/auth';

    /**
     * This endpoint is used to get a token for the user. The token is used to authenticate the user in the system. When
     * the token expires, the user must request a new token.
     *
     * @throws GuzzleException
     */
    public function token(string $username, string $password): TokenOutput
    {
        $httpClient = $this->client->getHttpClient()->createHttpClient();

        $response = $httpClient->post($this->formatUrl('/token'), [
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);

        /** @var TokenOutput $result */
        $result = $this->deserialize($response->getBody()->getContents(), TokenOutput::class);

        $this->client->addToken($result->accessToken);

        return $result;
    }

    /**
     * Exchanges a refresh token for a new access token. The refresh token is rotated: the one returned here
     * replaces it and must be used for the next refresh, since the submitted one is consumed and reusing it
     * revokes the whole session.
     *
     * @throws GuzzleException
     */
    public function refresh(string $refreshToken): TokenOutput
    {
        $httpClient = $this->client->getHttpClient()->createHttpClient();

        $response = $httpClient->post($this->formatUrl('/refresh'), [
            'json' => [
                'refresh_token' => $refreshToken,
            ],
        ]);

        /** @var TokenOutput $result */
        $result = $this->deserialize($response->getBody()->getContents(), TokenOutput::class);

        $this->client->addToken($result->accessToken);

        return $result;
    }

    /**
     * Revokes the session the refresh token belongs to. Already-issued access tokens stay valid until they expire.
     *
     * @throws GuzzleException
     */
    public function logout(string $refreshToken): void
    {
        $httpClient = $this->client->getHttpClient()->createHttpClient();

        $httpClient->post($this->formatUrl('/logout'), [
            'json' => [
                'refresh_token' => $refreshToken,
            ],
        ]);
    }

    /**
     * This endpoint is used to get a list of available permissions in the system. The permissions are used to define
     * the access rights of the users in the system. The permissions are defined by the system administrator.
     *
     * @return array<int|string, mixed>
     * @throws GuzzleException
     */
    public function getAvailablePermissions(): array
    {
        $endpoint = $this->formatUrl('/available-permissions');
        $response = $this->getHttpClient()->get($endpoint);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                sprintf('Failed to fetch data from endpoint %s: %s', $endpoint, $response->getReasonPhrase())
            );
        }

        return $this->client->getSerializer()->decode($response->getBody()->getContents(), 'json');
    }

    public function me(string $token): MeOutput
    {
        $this->client->addToken($token);
        $response = $this->getHttpClient()->get($this->formatUrl('/me'));
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                sprintf('Failed to fetch data from /me: %s', $response->getReasonPhrase())
            );
        }

        $data = $this->client->getSerializer()->decode($response->getBody()->getContents(), 'json');

        if (isset($data['agents']) && is_array($data['agents'])) {
            foreach ($data['agents'] as &$agent) {
                if (isset($agent['user'])) {
                    $agent['user'] = $this->deserialize(
                        $this->client->getSerializer()->encode($agent['user'], 'json'),
                        UserMeOutput::class
                    );
                }
                $agent = $this->deserialize(
                    $this->client->getSerializer()->encode($agent, 'json'),
                    AgentMatchOutput::class
                );
            }
        }

        return $this->deserialize($this->client->getSerializer()->encode($data, 'json'), MeOutput::class);
    }
}
<?php

namespace DataMat\GrinningCat\DTO\Api;

use Symfony\Component\Serializer\Annotation\SerializedName;

class TokenOutput
{
    #[SerializedName('access_token')]
    public string $accessToken;

    #[SerializedName('token_type')]
    public string $tokenType = 'bearer';

    #[SerializedName('expires_in')]
    public ?int $expiresIn = null;

    #[SerializedName('refresh_token')]
    public ?string $refreshToken = null;

    #[SerializedName('refresh_expires_in')]
    public ?int $refreshExpiresIn = null;
}
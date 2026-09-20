<?php

namespace DataMat\GrinningCat\DTO\Api\Plugin\Settings;

class PropertySettingsOutput
{
    public mixed $default = null;

    /** @var array<string, mixed>|null */
    public ?array $extra = null;

    public ?string $title = null;

    public ?string $type = null;

    public ?string $description = null;

    public ?array $enum = null;

    public ?string $format = null;
}

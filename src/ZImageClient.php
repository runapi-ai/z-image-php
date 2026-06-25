<?php

declare(strict_types=1);

namespace RunApi\ZImage;

use RunApi\Core\BaseClient;
use RunApi\Core\ClientOptions;
use RunApi\ZImage\Resources\TextToImage;

/**
 * The Z-Image text-to-image API client.
 *
 * Exposes typed model resources plus the universal files and account resources.
 */
final class ZImageClient extends BaseClient
{
    /**
     * Provides image generation operations.
     */
    public readonly TextToImage $textToImage;

    /**
     * Create a Z-Image client with optional API key, base URL, and transport overrides.
     */
    public function __construct(ClientOptions $options = new ClientOptions())
    {
        parent::__construct($options);
        $this->textToImage = TextToImage::fromHttp($this->http);
    }
}

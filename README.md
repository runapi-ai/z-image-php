# Z-Image PHP SDK for RunAPI

[![Packagist](https://img.shields.io/packagist/v/runapi-ai/z-image)](https://packagist.org/packages/runapi-ai/z-image)
[![License](https://img.shields.io/github/license/runapi-ai/z-image-php)](https://github.com/runapi-ai/z-image-php/blob/main/LICENSE)

The Z-Image PHP SDK is the Composer package for Z-Image on RunAPI. Use it when your PHP application needs associative-array request bodies, task status lookup, polling helpers, file helpers, and consistent RunAPI errors.

## Install

```bash
composer require runapi-ai/z-image
```

## Quick start

```php
<?php

require __DIR__ . "/vendor/autoload.php";

use RunApi\ZImage\ZImageClient;

$client = new ZImageClient(); // reads RUNAPI_API_KEY

$task = $client->textToImage->create([
    'model' => 'z-image',
    'prompt' => 'A serene Japanese garden at sunrise',
    'aspect_ratio' => '16:9',
]);

$status = $client->textToImage->get($task->id);

$result = $client->textToImage->run([
    'model' => 'z-image',
    'prompt' => 'A serene Japanese garden at sunrise',
    'aspect_ratio' => '16:9',
]);

echo $result->images[0]->url . PHP_EOL;
```

Use `create()` to submit a task and return quickly, `get()` to fetch the latest task state, and `run()` when a script should create and poll until completion. In web request handlers, prefer `create()` plus webhook or later `get()` polling so a worker is not held open.

Returned file URLs are temporary. Download and store generated files in your own durable storage within the retention window.

All SDK exceptions inherit from `RunApi\Core\Errors\RunApiException`, including validation, authentication, rate limit, task failure, and task timeout errors.

## Links

- Model page: https://runapi.ai/models/z-image
- SDK docs: https://runapi.ai/docs#sdk-z-image
- Product docs: https://runapi.ai/docs#z-image
- Pricing and rate limits: https://runapi.ai/models/z-image
- Full catalog: https://runapi.ai/models
- GitHub repository: https://github.com/runapi-ai/z-image-php
- Multi-language SDK repository: https://github.com/runapi-ai/z-image-sdk

## License

Licensed under the Apache License, Version 2.0.

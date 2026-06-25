<?php

declare(strict_types=1);

namespace RunApi\ZImage\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RunApi\Core\ClientOptions;
use RunApi\Core\Errors\ValidationException;
use RunApi\Core\Resources\TypedConfiguredResource;
use RunApi\Core\Tests\Fixtures\QueueHttpClient;
use RunApi\ZImage\Models\CompletedImageTaskResponse;
use RunApi\ZImage\Resources\TextToImage;
use RunApi\ZImage\ZImageClient;

final class ZImageClientTest extends TestCase
{
    public function testExposesTypedResources(): void
    {
        $client = new ZImageClient(new ClientOptions(apiKey: 'k', httpClient: new QueueHttpClient([]), maxRetries: 0));

        self::assertInstanceOf(TextToImage::class, $client->textToImage);
    }

    public function testTextToImageRunReturnsImages(): void
    {
        $transport = new QueueHttpClient([
            new Response(200, [], '{"id":"task_1"}'),
            new Response(200, [], '{"id":"task_1","status":"completed","images":[{"url":"https://file.runapi.ai/image.png"}],"extra_field":"kept"}'),
        ]);
        $client = new ZImageClient(new ClientOptions(apiKey: 'k', httpClient: $transport, maxRetries: 0));

        $result = $client->textToImage->run([
            'model' => 'z-image',
            'prompt' => 'A serene Japanese garden at sunrise',
            'aspect_ratio' => '16:9',
        ]);

        self::assertSame('https://file.runapi.ai/image.png', $result->images[0]->url);
        self::assertInstanceOf(CompletedImageTaskResponse::class, $result);
        self::assertSame('kept', $result->toArray()['extra_field']);
        self::assertSame('/api/v1/z_image/text_to_image', $transport->requests[0]->getUri()->getPath());
        self::assertSame('/api/v1/z_image/text_to_image/task_1', $transport->requests[1]->getUri()->getPath());
    }

    public function testTextToImageCreateCompactsRequestBody(): void
    {
        $transport = new QueueHttpClient([
            new Response(200, [], '{"id":"task_1"}'),
        ]);
        $client = new ZImageClient(new ClientOptions(apiKey: 'k', httpClient: $transport, maxRetries: 0));

        $this->createWithGenericParams($client->textToImage, [
            'model' => 'z-image',
            'prompt' => 'A serene Japanese garden at sunrise',
            'aspect_ratio' => '16:9',
            'callback_url' => '',
            'enable_safety_checker' => null,
        ]);

        self::assertSame('/api/v1/z_image/text_to_image', $transport->requests[0]->getUri()->getPath());
        self::assertJsonStringEqualsJsonString(
            '{"model":"z-image","prompt":"A serene Japanese garden at sunrise","aspect_ratio":"16:9"}',
            (string) $transport->requests[0]->getBody(),
        );
    }

    public function testRejectsUnknownModel(): void
    {
        $client = new ZImageClient(new ClientOptions(apiKey: 'k', httpClient: new QueueHttpClient([]), maxRetries: 0));

        $this->expectException(ValidationException::class);
        $client->textToImage->create([
            'model' => 'not-a-real-model',
            'prompt' => 'A serene Japanese garden at sunrise',
            'aspect_ratio' => '16:9',
        ]);
    }

    public function testRejectsPromptOutsideLengthRange(): void
    {
        $client = new ZImageClient(new ClientOptions(apiKey: 'k', httpClient: new QueueHttpClient([]), maxRetries: 0));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('prompt must be at most 1000 characters');

        $client->textToImage->create([
            'model' => 'z-image',
            'prompt' => str_repeat('a', 1001),
            'aspect_ratio' => '16:9',
        ]);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function createWithGenericParams(TypedConfiguredResource $resource, array $params): void
    {
        $resource->create($params);
    }
}

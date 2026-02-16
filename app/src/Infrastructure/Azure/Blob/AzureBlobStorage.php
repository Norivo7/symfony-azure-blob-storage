<?php

declare(strict_types=1);

namespace App\Infrastructure\Azure\Blob;

use App\Domain\Storage\BlobItem;
use App\Domain\Storage\BlobStorage;
use App\Domain\Storage\Exception\BlobNotFound;
use App\Domain\Storage\Exception\UploadFailed;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Symfony\Component\HttpFoundation\Response;

final readonly class AzureBlobStorage implements BlobStorage
{
    public function __construct(
        private BlobRestProxy $client,
        private string $container,
    ) {
    }

    public function list(string $prefix = ''): array
    {
        $options = new ListBlobsOptions();
        $options->setPrefix($prefix);

        $result = $this->client->listBlobs($this->container, $options);

        $items = [];
        foreach ($result->getBlobs() as $blob) {
            $props = $blob->getProperties();
            $date = \DateTimeImmutable::createFromMutable($props?->getLastModified());
            $items[] = new BlobItem(
                name: $blob->getName(),
                size: $blob->getProperties()->getContentLength(),
                contentType: $blob->getProperties()?->getContentType(),
                lastModified: $date
            );
        }

        return $items;
    }

    public function upload(string $blobName, string $localPath, string $contentType): void
    {
        $stream = @fopen($localPath, 'rb');
        if (false === $stream) {
            throw new UploadFailed("Cannot open file: {$localPath}");
        }

        try {
            $options = new CreateBlockBlobOptions();
            $options->setContentType($contentType);

            $this->client->createBlockBlob($this->container, $blobName, $stream, $options);
        } catch (ServiceException $exception) {
            throw new UploadFailed("Failed to upload blob: $blobName. Error: ".$exception->getMessage());
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function download(string $blobName): array
    {
        try {
            $result = $this->client->getBlob($this->container, $blobName);
        } catch (ServiceException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
                throw new BlobNotFound("Blob not found: {$blobName}", 404, $exception);
            }
            throw $exception;
        }

        $props = $result->getProperties();

        return [
            'stream' => $result->getContentStream(),
            'contentType' => $props?->getContentType() ?? 'application/octet-stream',
            'contentLength' => $props?->getContentLength(),
        ];
    }
}

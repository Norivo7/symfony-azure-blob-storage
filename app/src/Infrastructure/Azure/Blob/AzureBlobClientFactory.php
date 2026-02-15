<?php

declare(strict_types=1);

namespace App\Infrastructure\Azure\Blob;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

final readonly class AzureBlobClientFactory
{
    public function __construct(
        private string $accountName,
        private string $accountKey,
        private string $blobEndpoint
    ) { }

    public function create(): BlobRestProxy
    {
        $connectionString = sprintf(
            'DefaultEndpointsProtocol=http;AccountName=%s;AccountKey=%s;BlobEndpoint=%s;',
            $this->accountName,
            $this->accountKey,
            $this->blobEndpoint
        );

        return BlobRestProxy::createBlobService($connectionString);
    }
}

<?php
declare(strict_types=1);

namespace App\Application;

use App\Domain\Storage\BlobStorage;

final readonly class DownloadBlob
{
    public function __construct(private BlobStorage $storage) {}

    public function __invoke(string $blobName): array
    {
        return $this->storage->download($blobName);
    }
}

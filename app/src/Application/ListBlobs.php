<?php
declare(strict_types=1);

namespace App\Application;

use App\Domain\Storage\BlobStorage;

final readonly class ListBlobs
{
    public function __construct(private BlobStorage $storage) {}

    public function __invoke(string $prefix = ''): array
    {
        return $this->storage->list($prefix);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Storage;

final readonly class BlobItem
{
    public function __construct(
        public string $name,
        public int $size,
        public string $contentType,
        public \DateTimeImmutable $lastModified,
    ) {
    }
}

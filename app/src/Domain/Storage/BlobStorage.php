<?php
declare(strict_types=1);

namespace App\Domain\Storage;

interface BlobStorage
{
    public function list(string $prefix = ''): array;

    public function upload(string $blobName, string $localPath, string $contentType): void;

    public function download(string $blobName): array;
}

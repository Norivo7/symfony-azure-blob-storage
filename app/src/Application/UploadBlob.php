<?php
declare(strict_types=1);

namespace App\Application;

use App\Domain\Storage\BlobStorage;
use App\Domain\Storage\Exception\UploadFailed;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class UploadBlob
{
    public function __construct(private BlobStorage $storage) {}

    public function __invoke(UploadedFile $file, string $prefix = ''): string
    {
        if (!$file->isValid()) {
            throw new UploadFailed('File upload failed: '.$file->getErrorMessage());
        }

        $name = $this->sanitize($file->getClientOriginalName());
        $prefix = trim($prefix, '/');
        $blobName = $this->buildBlobName($name, $prefix);

        $contentType = $file->getClientMimeType() ?: 'application/octet-stream';

        $this->storage->upload($blobName, $file->getPathname(), $contentType);

        return $blobName;
    }

    private function sanitize(string $name): string
    {
        $name = basename($name);
        $name = str_replace(' ', '-', $name);
        return $name !== '' ? $name : 'file';
    }

    private function buildBlobName(string $name, string $prefix): string
    {
        $prefix = trim($prefix, '/');

        if ($prefix === '') {
            return $name;
        }

        return $prefix . '/' . $name;
    }
}

<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\ListBlobs;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TestController
{
    #[Route('/health/storage', name: 'health_storage', methods: ['GET'])]
    public function storage(ListBlobs $listBlobs): JsonResponse
    {
        $blobs = $listBlobs('');

        $names = [];
        foreach ($blobs as $blob) {
            $names[] = $blob->name;
        }

        return new JsonResponse([
            'ok' => true,
            'count' => count($blobs),
            'blobs' => $names,
        ]);
    }
}

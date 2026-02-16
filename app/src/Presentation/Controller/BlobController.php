<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\DownloadBlob;
use App\Application\ListBlobs;
use App\Application\UploadBlob;
use App\Domain\Storage\Exception\BlobNotFound;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class BlobController extends AbstractController
{
    #[Route('/', name: 'blob_index', methods: ['GET', 'POST'])]
    public function index(Request $request, ListBlobs $listBlobs, UploadBlob $uploadBlob): Response
    {
        $prefix = $request->get('prefix', '');
        $prefix = trim($prefix);

        if ($request->isMethod('POST')) {
            $file = $request->files->get('file');

            if (!$file instanceof UploadedFile) {
                $this->addFlash('error', 'No file selected.');
                return $this->redirectToRoute('blob_index', ['prefix' => $prefix]);
            }

            try {
                $blobName = $uploadBlob($file, $prefix);
                $this->addFlash('success', 'Uploaded: '.$blobName);
            } catch (\Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('blob_index', ['prefix' => $prefix]);
        }

        $blobs = $listBlobs($prefix);

        return $this->render('blob/index.html.twig', [
            'prefix' => $prefix,
            'blobs' => $blobs,
        ]);
    }

    #[Route('/download/{blobName}', name: 'blob_download', requirements: ['blobName' => '.+'], methods: ['GET'])]
    public function download(string $blobName, DownloadBlob $downloadBlob): Response
    {
        try {
            $data = $downloadBlob($blobName);
        } catch (BlobNotFound) {
            throw $this->createNotFoundException();
        }

        $response = new StreamedResponse(function () use ($data) {
            fpassthru($data['stream']);
        });

        $response->headers->set('Content-Type', $data['contentType']);
        if ($data['contentLength'] !== null) {
            $response->headers->set('Content-Length', (string) $data['contentLength']);
        }
        $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($blobName).'"');

        return $response;
    }
}

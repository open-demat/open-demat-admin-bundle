<?php

namespace OpenDemat\AdminBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenDemat\Core\Entity\Document;
use OpenDemat\Core\Entity\ProcessAttachment;
use OpenDemat\Core\Repository\ProcessAttachmentRepository;
use OpenDemat\Core\Service\AttachmentService;

#[Route('/admin/attachments')]
final class AttachmentAdminController extends AbstractController
{
    #[Route('/', name: 'open_demat_admin_attachment_index', methods: ['GET'])]
    public function index(ProcessAttachmentRepository $repo, Request $request): Response
    {
        $process = trim((string) $request->query->get('process', ''));
        $type    = trim((string) $request->query->get('type', ''));
        $q       = trim((string) $request->query->get('q', ''));

        $availableProcesses = $repo->listDistinctProcesses();
        $availableTypes     = $process !== '' ? $repo->listDistinctTypesForProcess($process) : [];

        $attachments = [];
        if ($process !== '' && $type !== '') {
            $attachments = $repo->searchForAdmin(
                processName: $process,
                caseType: $type,
                q: $q,
                limit: 500,
            );
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/attachment/index.html.twig', [
            'process'            => $process,
            'type'               => $type,
            'q'                  => $q,
            'availableProcesses' => $availableProcesses,
            'availableTypes'     => $availableTypes,
            'attachments'        => $attachments,
        ]);
    }

    #[Route('/{id}/update', name: 'open_demat_admin_attachment_update', methods: ['POST'])]
    public function update(
        ProcessAttachment $attachment,
        Request $request,
        AttachmentService $attachmentsSvc,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid('update_attachment_' . $attachment->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('open_demat_admin_attachment_index', $this->keepFilters($request));
        }

        // Renommage
        $newName = trim((string) $request->request->get('originalName', ''));
        if ($newName !== '') {
            $attachment->getDocument()->setOriginalName(
                preg_replace('/\s+/', ' ', $newName)
            );
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if ($file instanceof UploadedFile) {
            if (!$file->isValid()) {
                $this->addFlash('danger', 'Upload invalide.');
                return $this->redirectToRoute('open_demat_admin_attachment_index', $this->keepFilters($request));
            }

            $user = $this->getUser();
            $uploadedBy = $user instanceof \OpenDemat\Core\Entity\User ? $user : null;

            $attachmentsSvc->replaceAttachmentFile(
                attachment: $attachment,
                newFile: $file,
                uploadedBy: $uploadedBy,
                keepOriginalName: true,
            );
        } else {
            // rename only
            $em->flush();
        }

        $this->addFlash('success', 'Pièce jointe mise à jour.');
        return $this->redirectToRoute('open_demat_admin_attachment_index', $this->keepFilters($request));
    }

    #[Route('/document/{id}', name: 'open_demat_admin_document_view', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function viewDocument(
        Document $document,
        AttachmentService $attachmentService
    ): StreamedResponse {
        $stream = $attachmentService->openStream($document);

        $response = new StreamedResponse(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        });

        $mime = $document->getMimeType() ?: 'application/octet-stream';
        $response->headers->set('Content-Type', $mime);
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        $original = $document->getOriginalName() ?: 'document';
        $fallback = $this->asciiFallbackFilename($original);

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $original,
                $fallback
            )
        );

        return $response;
    }

    private function keepFilters(Request $request): array
    {
        return [
            'process' => (string) $request->query->get('process', $request->request->get('process', '')),
            'type'    => (string) $request->query->get('type', $request->request->get('type', '')),
            'q'       => (string) $request->query->get('q', $request->request->get('q', '')),
        ];
    }

    private function asciiFallbackFilename(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: 'document';
        $ascii = preg_replace('/[^A-Za-z0-9.\-_\s]/', '', $ascii) ?: 'document';
        $ascii = preg_replace('/\s+/', ' ', trim($ascii)) ?: 'document';

        if (!str_contains($ascii, '.')) {
            $ascii .= '.bin';
        }

        return $ascii;
    }
}

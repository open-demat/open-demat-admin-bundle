<?php

namespace OpenDemat\AdminBundle\Tests\Controller;

use OpenDemat\AdminBundle\Controller\AttachmentAdminController;
use OpenDemat\Core\Entity\Document;
use OpenDemat\Core\Service\AttachmentService;
use PHPUnit\Framework\TestCase;

final class DocumentDownloadTest extends TestCase
{
    public function testClientProvidedHtmlMimeTypeIsForcedToDownload(): void
    {
        $document = new Document('page.html', 'text/html', 10, 'documents');
        $attachments = $this->createStub(AttachmentService::class);
        $stream = fopen('php://temp', 'r+b');
        $attachments->method('openStream')->willReturn($stream);

        try {
            $response = (new AttachmentAdminController())->viewDocument($document, $attachments);
            self::assertSame('application/octet-stream', $response->headers->get('Content-Type'));
            self::assertStringStartsWith('attachment;', $response->headers->get('Content-Disposition'));
            self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
            self::assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        } finally {
            fclose($stream);
        }
    }
}

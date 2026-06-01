<?php

namespace OpenDemat\AdminBundle\Controller;

use OpenDemat\AdminBundle\Service\AdminGlobalConfiguration;
use OpenDemat\AdminBundle\Service\AdminGlobalConfigurationWriter;
use OpenDemat\Core\Service\StaticDocumentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    private const ORGANIZATION_LOGO_CODE = 'open_demat.organization_logo';
    private const ORGANIZATION_LOGO_ASSET_BASENAME = 'organization-logo';
    private const ORGANIZATION_LOGO_ASSET_DIR = 'assets/generated';

    #[Route('/', name: 'open_demat_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('@OpenDemat/admin-bundle/src/templates/dashboard.html.twig');
    }

    #[Route('/configuration', name: 'open_demat_admin_configuration', methods: ['GET'])]
    public function configuration(AdminGlobalConfiguration $configuration): Response
    {
        return $this->render('@OpenDemat/admin-bundle/src/templates/configuration/index.html.twig', [
            'sections' => $configuration->sections(),
            'formValues' => $configuration->formValues(),
        ]);
    }

    #[Route('/configuration', name: 'open_demat_admin_configuration_update', methods: ['POST'])]
    public function updateConfiguration(
        Request $request,
        AdminGlobalConfiguration $configuration,
        AdminGlobalConfigurationWriter $writer,
        StaticDocumentService $staticDocuments,
    ): Response {
        if (!$this->isCsrfTokenValid('open_demat_admin_configuration', (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');

            return $this->redirectToRoute('open_demat_admin_configuration');
        }

        $values = $configuration->currentValues();
        $organization = $request->request->all('organization');
        $theme = $request->request->all('theme');
        $cas = $request->request->all('cas');
        $s3 = $request->request->all('s3');

        $values['ORGANIZATION_NAME'] = trim((string) ($organization['name'] ?? ''));
        $values['THEME_PRIMARY_COLOR'] = $this->sanitizeHexColor((string) ($theme['primary_color'] ?? ''), '#E42535');
        $values['THEME_PRIMARY_DARK_COLOR'] = $this->sanitizeHexColor((string) ($theme['primary_dark_color'] ?? ''), '#B51E2A');

        $logo = $request->files->get('organization_logo');
        if ($logo instanceof UploadedFile) {
            if (!in_array($logo->getClientMimeType(), ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'], true)) {
                $this->addFlash('danger', 'Le logo doit être une image PNG, JPEG, SVG ou WebP.');

                return $this->redirectToRoute('open_demat_admin_configuration');
            }

            try {
                $staticDocument = $staticDocuments->storeUploadedFile(
                    code: self::ORGANIZATION_LOGO_CODE,
                    scope: 'open_demat',
                    label: 'Logo de l organisation',
                    file: $logo,
                );

                $projectDir = (string) $this->getParameter('kernel.project_dir');
                $relativeLogoPath = self::ORGANIZATION_LOGO_ASSET_DIR . '/' . self::ORGANIZATION_LOGO_ASSET_BASENAME . '.' . $this->extensionForMimeType($logo->getClientMimeType());

                $this->removePreviousLogoAssets($projectDir);
                $staticDocuments->materializeToPath(
                    $staticDocument,
                    $projectDir . '/public/' . $relativeLogoPath
                );

                $values['ORGANIZATION_LOGO'] = $relativeLogoPath;
            } catch (\Throwable $exception) {
                $this->addFlash('danger', 'Logo non enregistré : ' . $exception->getMessage());

                return $this->redirectToRoute('open_demat_admin_configuration');
            }
        }

        $values['CAS_BASE_URL'] = trim((string) ($cas['base_url'] ?? ''));
        $values['CAS_LOGOUT_URL'] = trim((string) ($cas['logout_url'] ?? ''));
        $values['CAS_HOST'] = trim((string) ($cas['host'] ?? ''));
        $values['CAS_PORT'] = trim((string) ($cas['port'] ?? ''));
        $values['CAS_PATH'] = trim((string) ($cas['path'] ?? ''));
        $values['CAS_LOGIN_TARGET'] = trim((string) ($cas['login_target'] ?? ''));
        $values['CAS_GATEWAY'] = ((string) ($cas['gateway'] ?? '0')) === '1' ? '1' : '0';
        $values['MINIO_ENDPOINT'] = trim((string) ($s3['endpoint'] ?? ''));
        $values['MINIO_REGION'] = trim((string) ($s3['region'] ?? ''));
        $values['MINIO_BUCKET'] = trim((string) ($s3['bucket'] ?? ''));
        $values['MINIO_USE_PATH_STYLE'] = ((string) ($s3['use_path_style'] ?? '0')) === '1' ? '1' : '0';

        $accessKey = trim((string) ($s3['access_key'] ?? ''));
        if ($accessKey !== '') {
            $values['MINIO_ACCESS_KEY'] = $accessKey;
        }

        $secretKey = trim((string) ($s3['secret_key'] ?? ''));
        if ($secretKey !== '') {
            $values['MINIO_SECRET_KEY'] = $secretKey;
        }

        $writer->save($values);

        $this->addFlash('success', 'Configuration enregistrée dans .env.local. Redémarrez le serveur Symfony pour appliquer les valeurs injectées au container.');

        return $this->redirectToRoute('open_demat_admin_configuration');
    }

    private function extensionForMimeType(?string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp',
            default => 'png',
        };
    }

    private function sanitizeHexColor(string $value, string $fallback): string
    {
        $value = trim($value);

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1 ? strtoupper($value) : $fallback;
    }

    private function removePreviousLogoAssets(string $projectDir): void
    {
        foreach (['png', 'jpg', 'svg', 'webp'] as $extension) {
            $path = $projectDir . '/public/' . self::ORGANIZATION_LOGO_ASSET_DIR . '/' . self::ORGANIZATION_LOGO_ASSET_BASENAME . '.' . $extension;

            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}

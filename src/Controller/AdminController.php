<?php

namespace OpenDemat\AdminBundle\Controller;

use OpenDemat\AdminBundle\Service\AdminGlobalConfiguration;
use OpenDemat\AdminBundle\Service\AdminGlobalConfigurationWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
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
    ): Response {
        if (!$this->isCsrfTokenValid('open_demat_admin_configuration', (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');

            return $this->redirectToRoute('open_demat_admin_configuration');
        }

        $values = $configuration->currentValues();
        $organization = $request->request->all('organization');
        $cas = $request->request->all('cas');
        $s3 = $request->request->all('s3');

        $values['ORGANIZATION_NAME'] = trim((string) ($organization['name'] ?? ''));
        $values['ORGANIZATION_LOGO'] = trim((string) ($organization['logo'] ?? ''));
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
}

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
    private const DEFAULT_THEME_PRIMARY_COLOR = '#E30613';
    private const DEFAULT_THEME_PRIMARY_DARK_COLOR = '#15202B';
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
        $local = $request->request->all('local');
        $mailer = $request->request->all('mailer');
        $cas = $request->request->all('cas');
        $ldap = $request->request->all('ldap');
        $saml2 = $request->request->all('saml2');
        $s3 = $request->request->all('s3');

        $values['ORGANIZATION_NAME'] = trim((string) ($organization['name'] ?? ''));
        if ($request->request->getBoolean('reset_theme')) {
            $values['THEME_PRIMARY_COLOR'] = self::DEFAULT_THEME_PRIMARY_COLOR;
            $values['THEME_PRIMARY_DARK_COLOR'] = self::DEFAULT_THEME_PRIMARY_DARK_COLOR;
        } else {
            $values['THEME_PRIMARY_COLOR'] = $this->sanitizeHexColor((string) ($theme['primary_color'] ?? ''), self::DEFAULT_THEME_PRIMARY_COLOR);
            $values['THEME_PRIMARY_DARK_COLOR'] = $this->sanitizeHexColor((string) ($theme['primary_dark_color'] ?? ''), self::DEFAULT_THEME_PRIMARY_DARK_COLOR);
        }

        $deleteLogo = $request->request->getBoolean('delete_logo');
        $logo = $request->files->get('organization_logo');
        if ($deleteLogo) {
            try {
                $staticDocuments->deactivateByCode(self::ORGANIZATION_LOGO_CODE);
                $this->removePreviousLogoAssets((string) $this->getParameter('kernel.project_dir'));
                $values['ORGANIZATION_LOGO'] = 'assets/img/open-demat-logo.png';
            } catch (\Throwable $exception) {
                $this->addFlash('danger', 'Logo non supprimé : ' . $exception->getMessage());

                return $this->redirectToRoute('open_demat_admin_configuration');
            }
        } elseif ($logo instanceof UploadedFile) {
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

        $adminUsername = trim((string) ($local['admin_username'] ?? ''));
        $values['LOCAL_ADMIN_USERNAME'] = $adminUsername !== '' ? $adminUsername : 'admin';
        $adminPassword = trim((string) ($local['admin_password'] ?? ''));
        if ($adminPassword !== '') {
            $values['LOCAL_ADMIN_PASSWORD'] = $adminPassword;
        }

        $values['MAILER_DSN'] = trim((string) ($mailer['dsn'] ?? 'smtp://localhost:1025'));
        $values['MAILER_FROM'] = trim((string) ($mailer['from'] ?? 'noreply@open-demat.example.org'));

        $values['CAS_ENABLED'] = $this->booleanEnv($cas['enabled'] ?? '0');
        $values['CAS_BASE_URL'] = trim((string) ($cas['base_url'] ?? ''));
        $values['CAS_LOGOUT_URL'] = trim((string) ($cas['logout_url'] ?? ''));
        $values['CAS_HOST'] = trim((string) ($cas['host'] ?? ''));
        $values['CAS_PORT'] = trim((string) ($cas['port'] ?? ''));
        $values['CAS_PATH'] = trim((string) ($cas['path'] ?? ''));
        $values['CAS_LOGIN_TARGET'] = trim((string) ($cas['login_target'] ?? ''));
        $values['CAS_GATEWAY'] = $this->booleanEnv($cas['gateway'] ?? '0');

        $values['LDAP_ENABLED'] = $this->booleanEnv($ldap['enabled'] ?? '0');
        $values['LDAP_CONNECTION_STRING'] = trim((string) ($ldap['connection_string'] ?? 'ldap://localhost:389'));
        $values['LDAP_BASE_DN'] = trim((string) ($ldap['base_dn'] ?? 'dc=example,dc=org'));
        $values['LDAP_SEARCH_FILTER'] = trim((string) ($ldap['search_filter'] ?? '(uid=%s)'));
        $values['LDAP_BIND_DN'] = trim((string) ($ldap['bind_dn'] ?? ''));
        $ldapBindPassword = trim((string) ($ldap['bind_password'] ?? ''));
        if ($ldapBindPassword !== '') {
            $values['LDAP_BIND_PASSWORD'] = $ldapBindPassword;
        }
        $values['LDAP_IDENTIFIER_ATTRIBUTE'] = trim((string) ($ldap['identifier_attribute'] ?? 'uid'));
        $values['LDAP_EMAIL_ATTRIBUTE'] = trim((string) ($ldap['email_attribute'] ?? 'mail'));
        $values['LDAP_FIRST_NAME_ATTRIBUTE'] = trim((string) ($ldap['first_name_attribute'] ?? 'givenName'));
        $values['LDAP_LAST_NAME_ATTRIBUTE'] = trim((string) ($ldap['last_name_attribute'] ?? 'sn'));
        $values['LDAP_AUTO_CREATE_USER'] = $this->booleanEnv($ldap['auto_create_user'] ?? '0');

        $values['SAML2_ENABLED'] = $this->booleanEnv($saml2['enabled'] ?? '0');
        $values['SAML2_STRICT'] = $this->booleanEnv($saml2['strict'] ?? '1');
        $values['SAML2_DEBUG'] = $this->booleanEnv($saml2['debug'] ?? '0');
        $values['SAML2_SP_ENTITY_ID'] = trim((string) ($saml2['sp_entity_id'] ?? ''));
        $values['SAML2_SP_ACS_URL'] = trim((string) ($saml2['sp_acs_url'] ?? ''));
        $values['SAML2_SP_SLS_URL'] = trim((string) ($saml2['sp_sls_url'] ?? ''));
        $values['SAML2_SP_NAME_ID_FORMAT'] = trim((string) ($saml2['sp_name_id_format'] ?? 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'));
        $this->replaceWhenFilled($values, 'SAML2_SP_X509_CERT', $saml2['sp_x509_cert'] ?? '');
        $this->replaceWhenFilled($values, 'SAML2_SP_PRIVATE_KEY', $saml2['sp_private_key'] ?? '');
        $values['SAML2_IDP_METADATA_FILE'] = trim((string) ($saml2['idp_metadata_file'] ?? ''));
        $values['SAML2_IDP_METADATA_URL'] = trim((string) ($saml2['idp_metadata_url'] ?? ''));
        $this->replaceWhenFilled($values, 'SAML2_IDP_METADATA_XML', $saml2['idp_metadata_xml'] ?? '');
        $values['SAML2_IDP_ENTITY_ID'] = trim((string) ($saml2['idp_entity_id'] ?? ''));
        $values['SAML2_IDP_SSO_URL'] = trim((string) ($saml2['idp_sso_url'] ?? ''));
        $values['SAML2_IDP_SLO_URL'] = trim((string) ($saml2['idp_slo_url'] ?? ''));
        $this->replaceWhenFilled($values, 'SAML2_IDP_X509_CERT', $saml2['idp_x509_cert'] ?? '');
        $values['SAML2_AUTHN_REQUESTS_SIGNED'] = $this->booleanEnv($saml2['authn_requests_signed'] ?? '0');
        $values['SAML2_LOGOUT_REQUEST_SIGNED'] = $this->booleanEnv($saml2['logout_request_signed'] ?? '0');
        $values['SAML2_LOGOUT_RESPONSE_SIGNED'] = $this->booleanEnv($saml2['logout_response_signed'] ?? '0');
        $values['SAML2_WANT_MESSAGES_SIGNED'] = $this->booleanEnv($saml2['want_messages_signed'] ?? '0');
        $values['SAML2_WANT_ASSERTIONS_SIGNED'] = $this->booleanEnv($saml2['want_assertions_signed'] ?? '0');
        $values['SAML2_WANT_ASSERTIONS_ENCRYPTED'] = $this->booleanEnv($saml2['want_assertions_encrypted'] ?? '0');
        $values['SAML2_WANT_NAME_ID_ENCRYPTED'] = $this->booleanEnv($saml2['want_name_id_encrypted'] ?? '0');
        $values['SAML2_SIGN_METADATA'] = $this->booleanEnv($saml2['sign_metadata'] ?? '0');
        $values['SAML2_IDENTIFIER_ATTRIBUTE'] = trim((string) ($saml2['identifier_attribute'] ?? 'uid'));
        $values['SAML2_EMAIL_ATTRIBUTE'] = trim((string) ($saml2['email_attribute'] ?? 'mail'));
        $values['SAML2_FIRST_NAME_ATTRIBUTE'] = trim((string) ($saml2['first_name_attribute'] ?? 'givenName'));
        $values['SAML2_LAST_NAME_ATTRIBUTE'] = trim((string) ($saml2['last_name_attribute'] ?? 'sn'));
        $values['SAML2_AUTO_CREATE_USER'] = $this->booleanEnv($saml2['auto_create_user'] ?? '0');
        $values['S3_ENDPOINT'] = trim((string) ($s3['endpoint'] ?? ''));
        $values['S3_REGION'] = trim((string) ($s3['region'] ?? ''));
        $values['S3_BUCKET'] = trim((string) ($s3['bucket'] ?? ''));
        $values['S3_USE_PATH_STYLE'] = $this->booleanEnv($s3['use_path_style'] ?? '0');

        $accessKey = trim((string) ($s3['access_key'] ?? ''));
        if ($accessKey !== '') {
            $values['S3_ACCESS_KEY'] = $accessKey;
        }

        $secretKey = trim((string) ($s3['secret_key'] ?? ''));
        if ($secretKey !== '') {
            $values['S3_SECRET_KEY'] = $secretKey;
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

    private function booleanEnv(mixed $value): string
    {
        return ((string) $value) === '1' ? '1' : '0';
    }

    /**
     * @param array<string, string> $values
     */
    private function replaceWhenFilled(array &$values, string $key, mixed $value): void
    {
        $value = trim((string) $value);
        if ($value !== '') {
            $values[$key] = $value;
        }
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

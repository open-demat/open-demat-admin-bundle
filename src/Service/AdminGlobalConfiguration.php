<?php

namespace OpenDemat\AdminBundle\Service;

final class AdminGlobalConfiguration
{
    /**
     * @param array<string, mixed> $organization
     * @param array<string, mixed> $theme
     * @param array<string, mixed> $cas
     * @param array<string, mixed> $saml2
     * @param array<string, mixed> $s3
     */
    public function __construct(
        private readonly array $organization,
        private readonly array $theme,
        private readonly array $cas,
        private readonly array $saml2,
        private readonly array $s3,
    ) {
    }

    /**
     * @return array<string, array<int, array{label: string, value: mixed, secret?: bool}>>
     */
    public function sections(): array
    {
        return [
            'Organisation' => [
                ['label' => 'Nom', 'value' => $this->organization['name'] ?? ''],
                ['label' => 'Logo', 'value' => $this->organization['logo'] ?? ''],
            ],
            'Theme' => [
                ['label' => 'Couleur principale', 'value' => $this->theme['primary_color'] ?? ''],
                ['label' => 'Couleur principale foncee', 'value' => $this->theme['primary_dark_color'] ?? ''],
            ],
            'CAS' => [
                ['label' => 'URL de base', 'value' => $this->cas['base_url'] ?? ''],
                ['label' => 'URL de deconnexion', 'value' => $this->cas['logout_url'] ?? ''],
                ['label' => 'Hote', 'value' => $this->cas['host'] ?? ''],
                ['label' => 'Port', 'value' => $this->cas['port'] ?? ''],
                ['label' => 'Chemin', 'value' => $this->cas['path'] ?? ''],
                ['label' => 'Cible de connexion', 'value' => $this->cas['login_target'] ?? ''],
                ['label' => 'Gateway', 'value' => $this->formatBoolean($this->cas['gateway'] ?? false)],
            ],
            'SAML2' => [
                ['label' => 'Active', 'value' => $this->formatBoolean($this->saml2['enabled'] ?? false)],
                ['label' => 'Attribut identifiant', 'value' => $this->saml2['identifier_attribute'] ?? ''],
                ['label' => 'Attribut email', 'value' => $this->saml2['email_attribute'] ?? ''],
                ['label' => 'Attribut prenom', 'value' => $this->saml2['first_name_attribute'] ?? ''],
                ['label' => 'Attribut nom', 'value' => $this->saml2['last_name_attribute'] ?? ''],
                ['label' => 'Domaine email par defaut', 'value' => $this->saml2['default_email_domain'] ?? ''],
                ['label' => 'Creation utilisateur auto', 'value' => $this->formatBoolean($this->saml2['auto_create_user'] ?? false)],
                ['label' => 'URL de connexion', 'value' => $this->saml2['login_url'] ?? ''],
            ],
            'S3' => [
                ['label' => 'Endpoint', 'value' => $this->s3['endpoint'] ?? ''],
                ['label' => 'Region', 'value' => $this->s3['region'] ?? ''],
                ['label' => 'Bucket', 'value' => $this->s3['bucket'] ?? ''],
                ['label' => 'Path style', 'value' => $this->formatBoolean($this->s3['use_path_style'] ?? false)],
                ['label' => 'Access key', 'value' => $this->maskValue((string) ($this->s3['access_key'] ?? '')), 'secret' => true],
                ['label' => 'Secret key', 'value' => $this->maskValue((string) ($this->s3['secret_key'] ?? '')), 'secret' => true],
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function formValues(): array
    {
        return [
            'organization' => [
                'name' => (string) ($this->organization['name'] ?? 'Open Demat'),
                'logo' => (string) ($this->organization['logo'] ?? 'assets/img/open-demat-logo.png'),
            ],
            'theme' => [
                'primary_color' => (string) ($this->theme['primary_color'] ?? '#E30613'),
                'primary_dark_color' => (string) ($this->theme['primary_dark_color'] ?? '#15202B'),
            ],
            'cas' => [
                'base_url' => (string) ($this->cas['base_url'] ?? ''),
                'logout_url' => (string) ($this->cas['logout_url'] ?? ''),
                'host' => (string) ($this->cas['host'] ?? ''),
                'port' => (string) ($this->cas['port'] ?? ''),
                'path' => (string) ($this->cas['path'] ?? ''),
                'login_target' => (string) ($this->cas['login_target'] ?? ''),
                'gateway' => $this->formatBooleanValue($this->cas['gateway'] ?? false),
            ],
            'saml2' => [
                'enabled' => $this->formatBooleanValue($this->saml2['enabled'] ?? false),
                'identifier_attribute' => (string) ($this->saml2['identifier_attribute'] ?? 'REMOTE_USER'),
                'email_attribute' => (string) ($this->saml2['email_attribute'] ?? 'mail'),
                'first_name_attribute' => (string) ($this->saml2['first_name_attribute'] ?? 'givenName'),
                'last_name_attribute' => (string) ($this->saml2['last_name_attribute'] ?? 'sn'),
                'default_email_domain' => (string) ($this->saml2['default_email_domain'] ?? ''),
                'auto_create_user' => $this->formatBooleanValue($this->saml2['auto_create_user'] ?? true),
                'login_url' => (string) ($this->saml2['login_url'] ?? ''),
            ],
            's3' => [
                'endpoint' => (string) ($this->s3['endpoint'] ?? ''),
                'region' => (string) ($this->s3['region'] ?? ''),
                'bucket' => (string) ($this->s3['bucket'] ?? ''),
                'use_path_style' => $this->formatBooleanValue($this->s3['use_path_style'] ?? true),
                'access_key' => '',
                'secret_key' => '',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function currentValues(): array
    {
        return [
            'ORGANIZATION_NAME' => (string) ($this->organization['name'] ?? 'Open Demat'),
            'ORGANIZATION_LOGO' => (string) ($this->organization['logo'] ?? 'assets/img/open-demat-logo.png'),
            'THEME_PRIMARY_COLOR' => (string) ($this->theme['primary_color'] ?? '#E30613'),
            'THEME_PRIMARY_DARK_COLOR' => (string) ($this->theme['primary_dark_color'] ?? '#15202B'),
            'CAS_BASE_URL' => (string) ($this->cas['base_url'] ?? ''),
            'CAS_LOGOUT_URL' => (string) ($this->cas['logout_url'] ?? ''),
            'CAS_HOST' => (string) ($this->cas['host'] ?? ''),
            'CAS_PORT' => (string) ($this->cas['port'] ?? ''),
            'CAS_PATH' => (string) ($this->cas['path'] ?? ''),
            'CAS_LOGIN_TARGET' => (string) ($this->cas['login_target'] ?? ''),
            'CAS_GATEWAY' => $this->formatBooleanValue($this->cas['gateway'] ?? false),
            'SAML2_ENABLED' => $this->formatBooleanValue($this->saml2['enabled'] ?? false),
            'SAML2_IDENTIFIER_ATTRIBUTE' => (string) ($this->saml2['identifier_attribute'] ?? 'REMOTE_USER'),
            'SAML2_EMAIL_ATTRIBUTE' => (string) ($this->saml2['email_attribute'] ?? 'mail'),
            'SAML2_FIRST_NAME_ATTRIBUTE' => (string) ($this->saml2['first_name_attribute'] ?? 'givenName'),
            'SAML2_LAST_NAME_ATTRIBUTE' => (string) ($this->saml2['last_name_attribute'] ?? 'sn'),
            'SAML2_DEFAULT_EMAIL_DOMAIN' => (string) ($this->saml2['default_email_domain'] ?? ''),
            'SAML2_AUTO_CREATE_USER' => $this->formatBooleanValue($this->saml2['auto_create_user'] ?? true),
            'SAML2_LOGIN_URL' => (string) ($this->saml2['login_url'] ?? ''),
            'S3_ENDPOINT' => (string) ($this->s3['endpoint'] ?? ''),
            'S3_REGION' => (string) ($this->s3['region'] ?? ''),
            'S3_BUCKET' => (string) ($this->s3['bucket'] ?? ''),
            'S3_USE_PATH_STYLE' => $this->formatBooleanValue($this->s3['use_path_style'] ?? true),
            'S3_ACCESS_KEY' => (string) ($this->s3['access_key'] ?? ''),
            'S3_SECRET_KEY' => (string) ($this->s3['secret_key'] ?? ''),
        ];
    }

    private function formatBoolean(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Oui' : 'Non';
    }

    private function formatBooleanValue(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
    }

    private function maskValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (strlen($value) <= 4) {
            return '********';
        }

        return substr($value, 0, 2) . '********' . substr($value, -2);
    }
}

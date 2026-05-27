<?php

namespace OpenDemat\AdminBundle\Service;

final class AdminGlobalConfiguration
{
    /**
     * @param array<string, mixed> $organization
     * @param array<string, mixed> $cas
     * @param array<string, mixed> $s3
     */
    public function __construct(
        private readonly array $organization,
        private readonly array $cas,
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
            'CAS' => [
                ['label' => 'URL de base', 'value' => $this->cas['base_url'] ?? ''],
                ['label' => 'URL de déconnexion', 'value' => $this->cas['logout_url'] ?? ''],
                ['label' => 'Hôte', 'value' => $this->cas['host'] ?? ''],
                ['label' => 'Port', 'value' => $this->cas['port'] ?? ''],
                ['label' => 'Chemin', 'value' => $this->cas['path'] ?? ''],
                ['label' => 'Cible de connexion', 'value' => $this->cas['login_target'] ?? ''],
                ['label' => 'Gateway', 'value' => $this->formatBoolean($this->cas['gateway'] ?? false)],
            ],
            'S3 / MinIO' => [
                ['label' => 'Endpoint', 'value' => $this->s3['endpoint'] ?? ''],
                ['label' => 'Région', 'value' => $this->s3['region'] ?? ''],
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
                'name' => (string) ($this->organization['name'] ?? ''),
                'logo' => (string) ($this->organization['logo'] ?? ''),
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
            'ORGANIZATION_NAME' => (string) ($this->organization['name'] ?? ''),
            'ORGANIZATION_LOGO' => (string) ($this->organization['logo'] ?? ''),
            'CAS_BASE_URL' => (string) ($this->cas['base_url'] ?? ''),
            'CAS_LOGOUT_URL' => (string) ($this->cas['logout_url'] ?? ''),
            'CAS_HOST' => (string) ($this->cas['host'] ?? ''),
            'CAS_PORT' => (string) ($this->cas['port'] ?? ''),
            'CAS_PATH' => (string) ($this->cas['path'] ?? ''),
            'CAS_LOGIN_TARGET' => (string) ($this->cas['login_target'] ?? ''),
            'CAS_GATEWAY' => $this->formatBooleanValue($this->cas['gateway'] ?? false),
            'MINIO_ENDPOINT' => (string) ($this->s3['endpoint'] ?? ''),
            'MINIO_REGION' => (string) ($this->s3['region'] ?? ''),
            'MINIO_BUCKET' => (string) ($this->s3['bucket'] ?? ''),
            'MINIO_USE_PATH_STYLE' => $this->formatBooleanValue($this->s3['use_path_style'] ?? true),
            'MINIO_ACCESS_KEY' => (string) ($this->s3['access_key'] ?? ''),
            'MINIO_SECRET_KEY' => (string) ($this->s3['secret_key'] ?? ''),
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

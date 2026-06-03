<?php

namespace OpenDemat\AdminBundle\Service;

final class AdminGlobalConfiguration
{
    /**
     * @param array<string, mixed> $organization
     * @param array<string, mixed> $theme
     * @param array<string, mixed> $local
     * @param array<string, mixed> $mailer
     * @param array<string, mixed> $cas
     * @param array<string, mixed> $ldap
     * @param array<string, mixed> $saml2
     * @param array<string, mixed> $s3
     */
    public function __construct(
        private readonly array $organization,
        private readonly array $theme,
        private readonly array $local,
        private readonly array $mailer,
        private readonly array $cas,
        private readonly array $ldap,
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
            'Authentification locale' => [
                ['label' => 'Login administrateur', 'value' => $this->local['admin_username'] ?? ''],
                ['label' => 'Mot de passe administrateur', 'value' => $this->maskValue((string) ($this->local['admin_password'] ?? '')), 'secret' => true],
            ],
            'Serveur mail' => [
                ['label' => 'DSN', 'value' => $this->mailer['dsn'] ?? ''],
                ['label' => 'Adresse expediteur', 'value' => $this->mailer['from'] ?? ''],
            ],
            'CAS' => [
                ['label' => 'Active', 'value' => $this->formatBoolean($this->cas['enabled'] ?? false)],
                ['label' => 'URL de base', 'value' => $this->cas['base_url'] ?? ''],
                ['label' => 'URL de deconnexion', 'value' => $this->cas['logout_url'] ?? ''],
                ['label' => 'Hote', 'value' => $this->cas['host'] ?? ''],
                ['label' => 'Port', 'value' => $this->cas['port'] ?? ''],
                ['label' => 'Chemin', 'value' => $this->cas['path'] ?? ''],
                ['label' => 'Cible de connexion', 'value' => $this->cas['login_target'] ?? ''],
                ['label' => 'Gateway', 'value' => $this->formatBoolean($this->cas['gateway'] ?? false)],
            ],
            'LDAP' => [
                ['label' => 'Active', 'value' => $this->formatBoolean($this->ldap['enabled'] ?? false)],
                ['label' => 'Connexion', 'value' => $this->ldap['connection_string'] ?? ''],
                ['label' => 'Base DN', 'value' => $this->ldap['base_dn'] ?? ''],
                ['label' => 'Filtre de recherche', 'value' => $this->ldap['search_filter'] ?? ''],
                ['label' => 'Bind DN', 'value' => $this->ldap['bind_dn'] ?? ''],
                ['label' => 'Bind password', 'value' => $this->maskValue((string) ($this->ldap['bind_password'] ?? '')), 'secret' => true],
                ['label' => 'Attribut identifiant', 'value' => $this->ldap['identifier_attribute'] ?? ''],
                ['label' => 'Attribut email', 'value' => $this->ldap['email_attribute'] ?? ''],
                ['label' => 'Attribut prenom', 'value' => $this->ldap['first_name_attribute'] ?? ''],
                ['label' => 'Attribut nom', 'value' => $this->ldap['last_name_attribute'] ?? ''],
                ['label' => 'Creation utilisateur auto', 'value' => $this->formatBoolean($this->ldap['auto_create_user'] ?? false)],
            ],
            'SAML2' => [
                ['label' => 'Active', 'value' => $this->formatBoolean($this->saml2['enabled'] ?? false)],
                ['label' => 'Strict', 'value' => $this->formatBoolean($this->saml2['strict'] ?? false)],
                ['label' => 'Debug', 'value' => $this->formatBoolean($this->saml2['debug'] ?? false)],
                ['label' => 'SP entity ID', 'value' => $this->saml2['sp_entity_id'] ?? ''],
                ['label' => 'SP ACS URL', 'value' => $this->saml2['sp_acs_url'] ?? ''],
                ['label' => 'SP SLS URL', 'value' => $this->saml2['sp_sls_url'] ?? ''],
                ['label' => 'SP NameID format', 'value' => $this->saml2['sp_name_id_format'] ?? ''],
                ['label' => 'SP certificat', 'value' => $this->maskValue((string) ($this->saml2['sp_x509_cert'] ?? '')), 'secret' => true],
                ['label' => 'SP cle privee', 'value' => $this->maskValue((string) ($this->saml2['sp_private_key'] ?? '')), 'secret' => true],
                ['label' => 'IdP metadata file', 'value' => $this->saml2['idp_metadata_file'] ?? ''],
                ['label' => 'IdP metadata URL', 'value' => $this->saml2['idp_metadata_url'] ?? ''],
                ['label' => 'IdP metadata XML', 'value' => $this->maskValue((string) ($this->saml2['idp_metadata_xml'] ?? '')), 'secret' => true],
                ['label' => 'IdP entity ID', 'value' => $this->saml2['idp_entity_id'] ?? ''],
                ['label' => 'IdP SSO URL', 'value' => $this->saml2['idp_sso_url'] ?? ''],
                ['label' => 'IdP SLO URL', 'value' => $this->saml2['idp_slo_url'] ?? ''],
                ['label' => 'IdP certificat', 'value' => $this->maskValue((string) ($this->saml2['idp_x509_cert'] ?? '')), 'secret' => true],
                ['label' => 'AuthnRequest signee', 'value' => $this->formatBoolean($this->saml2['authn_requests_signed'] ?? false)],
                ['label' => 'LogoutRequest signee', 'value' => $this->formatBoolean($this->saml2['logout_request_signed'] ?? false)],
                ['label' => 'LogoutResponse signee', 'value' => $this->formatBoolean($this->saml2['logout_response_signed'] ?? false)],
                ['label' => 'Messages signes requis', 'value' => $this->formatBoolean($this->saml2['want_messages_signed'] ?? false)],
                ['label' => 'Assertions signees requises', 'value' => $this->formatBoolean($this->saml2['want_assertions_signed'] ?? false)],
                ['label' => 'Assertions chiffrees requises', 'value' => $this->formatBoolean($this->saml2['want_assertions_encrypted'] ?? false)],
                ['label' => 'NameID chiffre requis', 'value' => $this->formatBoolean($this->saml2['want_name_id_encrypted'] ?? false)],
                ['label' => 'Metadata signee', 'value' => $this->formatBoolean($this->saml2['sign_metadata'] ?? false)],
                ['label' => 'Attribut identifiant', 'value' => $this->saml2['identifier_attribute'] ?? ''],
                ['label' => 'Attribut email', 'value' => $this->saml2['email_attribute'] ?? ''],
                ['label' => 'Attribut prenom', 'value' => $this->saml2['first_name_attribute'] ?? ''],
                ['label' => 'Attribut nom', 'value' => $this->saml2['last_name_attribute'] ?? ''],
                ['label' => 'Creation utilisateur auto', 'value' => $this->formatBoolean($this->saml2['auto_create_user'] ?? false)],
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
            'local' => [
                'admin_username' => (string) ($this->local['admin_username'] ?? 'admin'),
                'admin_password' => '',
            ],
            'mailer' => [
                'dsn' => (string) ($this->mailer['dsn'] ?? 'smtp://localhost:1025'),
                'from' => (string) ($this->mailer['from'] ?? 'noreply@open-demat.example.org'),
            ],
            'cas' => [
                'enabled' => $this->formatBooleanValue($this->cas['enabled'] ?? false),
                'base_url' => (string) ($this->cas['base_url'] ?? ''),
                'logout_url' => (string) ($this->cas['logout_url'] ?? ''),
                'host' => (string) ($this->cas['host'] ?? ''),
                'port' => (string) ($this->cas['port'] ?? ''),
                'path' => (string) ($this->cas['path'] ?? ''),
                'login_target' => (string) ($this->cas['login_target'] ?? ''),
                'gateway' => $this->formatBooleanValue($this->cas['gateway'] ?? false),
            ],
            'ldap' => [
                'enabled' => $this->formatBooleanValue($this->ldap['enabled'] ?? false),
                'connection_string' => (string) ($this->ldap['connection_string'] ?? 'ldap://localhost:389'),
                'base_dn' => (string) ($this->ldap['base_dn'] ?? 'dc=example,dc=org'),
                'search_filter' => (string) ($this->ldap['search_filter'] ?? '(uid=%s)'),
                'bind_dn' => (string) ($this->ldap['bind_dn'] ?? ''),
                'bind_password' => '',
                'identifier_attribute' => (string) ($this->ldap['identifier_attribute'] ?? 'uid'),
                'email_attribute' => (string) ($this->ldap['email_attribute'] ?? 'mail'),
                'first_name_attribute' => (string) ($this->ldap['first_name_attribute'] ?? 'givenName'),
                'last_name_attribute' => (string) ($this->ldap['last_name_attribute'] ?? 'sn'),
                'auto_create_user' => $this->formatBooleanValue($this->ldap['auto_create_user'] ?? true),
            ],
            'saml2' => [
                'enabled' => $this->formatBooleanValue($this->saml2['enabled'] ?? false),
                'strict' => $this->formatBooleanValue($this->saml2['strict'] ?? true),
                'debug' => $this->formatBooleanValue($this->saml2['debug'] ?? false),
                'sp_entity_id' => (string) ($this->saml2['sp_entity_id'] ?? ''),
                'sp_acs_url' => (string) ($this->saml2['sp_acs_url'] ?? ''),
                'sp_sls_url' => (string) ($this->saml2['sp_sls_url'] ?? ''),
                'sp_name_id_format' => (string) ($this->saml2['sp_name_id_format'] ?? 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'),
                'sp_x509_cert' => '',
                'sp_private_key' => '',
                'idp_metadata_file' => (string) ($this->saml2['idp_metadata_file'] ?? ''),
                'idp_metadata_url' => (string) ($this->saml2['idp_metadata_url'] ?? ''),
                'idp_metadata_xml' => '',
                'idp_entity_id' => (string) ($this->saml2['idp_entity_id'] ?? ''),
                'idp_sso_url' => (string) ($this->saml2['idp_sso_url'] ?? ''),
                'idp_slo_url' => (string) ($this->saml2['idp_slo_url'] ?? ''),
                'idp_x509_cert' => '',
                'authn_requests_signed' => $this->formatBooleanValue($this->saml2['authn_requests_signed'] ?? false),
                'logout_request_signed' => $this->formatBooleanValue($this->saml2['logout_request_signed'] ?? false),
                'logout_response_signed' => $this->formatBooleanValue($this->saml2['logout_response_signed'] ?? false),
                'want_messages_signed' => $this->formatBooleanValue($this->saml2['want_messages_signed'] ?? false),
                'want_assertions_signed' => $this->formatBooleanValue($this->saml2['want_assertions_signed'] ?? false),
                'want_assertions_encrypted' => $this->formatBooleanValue($this->saml2['want_assertions_encrypted'] ?? false),
                'want_name_id_encrypted' => $this->formatBooleanValue($this->saml2['want_name_id_encrypted'] ?? false),
                'sign_metadata' => $this->formatBooleanValue($this->saml2['sign_metadata'] ?? false),
                'identifier_attribute' => (string) ($this->saml2['identifier_attribute'] ?? 'uid'),
                'email_attribute' => (string) ($this->saml2['email_attribute'] ?? 'mail'),
                'first_name_attribute' => (string) ($this->saml2['first_name_attribute'] ?? 'givenName'),
                'last_name_attribute' => (string) ($this->saml2['last_name_attribute'] ?? 'sn'),
                'auto_create_user' => $this->formatBooleanValue($this->saml2['auto_create_user'] ?? true),
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
            'LOCAL_ADMIN_USERNAME' => (string) ($this->local['admin_username'] ?? 'admin'),
            'LOCAL_ADMIN_PASSWORD' => (string) ($this->local['admin_password'] ?? 'admin'),
            'MAILER_DSN' => (string) ($this->mailer['dsn'] ?? 'smtp://localhost:1025'),
            'MAILER_FROM' => (string) ($this->mailer['from'] ?? 'noreply@open-demat.example.org'),
            'CAS_ENABLED' => $this->formatBooleanValue($this->cas['enabled'] ?? false),
            'CAS_BASE_URL' => (string) ($this->cas['base_url'] ?? ''),
            'CAS_LOGOUT_URL' => (string) ($this->cas['logout_url'] ?? ''),
            'CAS_HOST' => (string) ($this->cas['host'] ?? ''),
            'CAS_PORT' => (string) ($this->cas['port'] ?? ''),
            'CAS_PATH' => (string) ($this->cas['path'] ?? ''),
            'CAS_LOGIN_TARGET' => (string) ($this->cas['login_target'] ?? ''),
            'CAS_GATEWAY' => $this->formatBooleanValue($this->cas['gateway'] ?? false),
            'LDAP_ENABLED' => $this->formatBooleanValue($this->ldap['enabled'] ?? false),
            'LDAP_CONNECTION_STRING' => (string) ($this->ldap['connection_string'] ?? 'ldap://localhost:389'),
            'LDAP_BASE_DN' => (string) ($this->ldap['base_dn'] ?? 'dc=example,dc=org'),
            'LDAP_SEARCH_FILTER' => (string) ($this->ldap['search_filter'] ?? '(uid=%s)'),
            'LDAP_BIND_DN' => (string) ($this->ldap['bind_dn'] ?? ''),
            'LDAP_BIND_PASSWORD' => (string) ($this->ldap['bind_password'] ?? ''),
            'LDAP_IDENTIFIER_ATTRIBUTE' => (string) ($this->ldap['identifier_attribute'] ?? 'uid'),
            'LDAP_EMAIL_ATTRIBUTE' => (string) ($this->ldap['email_attribute'] ?? 'mail'),
            'LDAP_FIRST_NAME_ATTRIBUTE' => (string) ($this->ldap['first_name_attribute'] ?? 'givenName'),
            'LDAP_LAST_NAME_ATTRIBUTE' => (string) ($this->ldap['last_name_attribute'] ?? 'sn'),
            'LDAP_AUTO_CREATE_USER' => $this->formatBooleanValue($this->ldap['auto_create_user'] ?? true),
            'SAML2_ENABLED' => $this->formatBooleanValue($this->saml2['enabled'] ?? false),
            'SAML2_STRICT' => $this->formatBooleanValue($this->saml2['strict'] ?? true),
            'SAML2_DEBUG' => $this->formatBooleanValue($this->saml2['debug'] ?? false),
            'SAML2_SP_ENTITY_ID' => (string) ($this->saml2['sp_entity_id'] ?? ''),
            'SAML2_SP_ACS_URL' => (string) ($this->saml2['sp_acs_url'] ?? ''),
            'SAML2_SP_SLS_URL' => (string) ($this->saml2['sp_sls_url'] ?? ''),
            'SAML2_SP_NAME_ID_FORMAT' => (string) ($this->saml2['sp_name_id_format'] ?? 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'),
            'SAML2_SP_X509_CERT' => (string) ($this->saml2['sp_x509_cert'] ?? ''),
            'SAML2_SP_PRIVATE_KEY' => (string) ($this->saml2['sp_private_key'] ?? ''),
            'SAML2_IDP_METADATA_FILE' => (string) ($this->saml2['idp_metadata_file'] ?? ''),
            'SAML2_IDP_METADATA_URL' => (string) ($this->saml2['idp_metadata_url'] ?? ''),
            'SAML2_IDP_METADATA_XML' => (string) ($this->saml2['idp_metadata_xml'] ?? ''),
            'SAML2_IDP_ENTITY_ID' => (string) ($this->saml2['idp_entity_id'] ?? ''),
            'SAML2_IDP_SSO_URL' => (string) ($this->saml2['idp_sso_url'] ?? ''),
            'SAML2_IDP_SLO_URL' => (string) ($this->saml2['idp_slo_url'] ?? ''),
            'SAML2_IDP_X509_CERT' => (string) ($this->saml2['idp_x509_cert'] ?? ''),
            'SAML2_AUTHN_REQUESTS_SIGNED' => $this->formatBooleanValue($this->saml2['authn_requests_signed'] ?? false),
            'SAML2_LOGOUT_REQUEST_SIGNED' => $this->formatBooleanValue($this->saml2['logout_request_signed'] ?? false),
            'SAML2_LOGOUT_RESPONSE_SIGNED' => $this->formatBooleanValue($this->saml2['logout_response_signed'] ?? false),
            'SAML2_WANT_MESSAGES_SIGNED' => $this->formatBooleanValue($this->saml2['want_messages_signed'] ?? false),
            'SAML2_WANT_ASSERTIONS_SIGNED' => $this->formatBooleanValue($this->saml2['want_assertions_signed'] ?? false),
            'SAML2_WANT_ASSERTIONS_ENCRYPTED' => $this->formatBooleanValue($this->saml2['want_assertions_encrypted'] ?? false),
            'SAML2_WANT_NAME_ID_ENCRYPTED' => $this->formatBooleanValue($this->saml2['want_name_id_encrypted'] ?? false),
            'SAML2_SIGN_METADATA' => $this->formatBooleanValue($this->saml2['sign_metadata'] ?? false),
            'SAML2_IDENTIFIER_ATTRIBUTE' => (string) ($this->saml2['identifier_attribute'] ?? 'uid'),
            'SAML2_EMAIL_ATTRIBUTE' => (string) ($this->saml2['email_attribute'] ?? 'mail'),
            'SAML2_FIRST_NAME_ATTRIBUTE' => (string) ($this->saml2['first_name_attribute'] ?? 'givenName'),
            'SAML2_LAST_NAME_ATTRIBUTE' => (string) ($this->saml2['last_name_attribute'] ?? 'sn'),
            'SAML2_AUTO_CREATE_USER' => $this->formatBooleanValue($this->saml2['auto_create_user'] ?? true),
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

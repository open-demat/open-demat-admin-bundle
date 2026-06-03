<?php

namespace OpenDemat\AdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Yaml\Yaml;

class AdminExtension extends ConfigurableExtension implements PrependExtensionInterface
{
    public function getAlias(): string
    {
        return 'open_demat_admin';
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension($this->getAlias())) {
            return;
        }

        $config = Yaml::parseFile(__DIR__ . '/../../Resources/config/packages/open_demat_admin.yaml');
        if (isset($config[$this->getAlias()]) && is_array($config[$this->getAlias()])) {
            $container->prependExtensionConfig($this->getAlias(), $config[$this->getAlias()]);
        }
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->setParameter('open_demat_admin.organization.name', $mergedConfig['organization']['name']);
        $container->setParameter('open_demat_admin.organization.logo', $mergedConfig['organization']['logo']);
        $container->setParameter('open_demat_admin.theme.primary_color', $mergedConfig['theme']['primary_color']);
        $container->setParameter('open_demat_admin.theme.primary_dark_color', $mergedConfig['theme']['primary_dark_color']);
        $container->setParameter('open_demat_admin.local.admin_username', $mergedConfig['local']['admin_username']);
        $container->setParameter('open_demat_admin.local.admin_password', $mergedConfig['local']['admin_password']);
        $container->setParameter('open_demat_admin.mailer.dsn', $mergedConfig['mailer']['dsn']);
        $container->setParameter('open_demat_admin.mailer.from', $mergedConfig['mailer']['from']);
        $container->setParameter('open_demat_admin.cas.enabled', $mergedConfig['cas']['enabled']);
        $container->setParameter('open_demat_admin.cas.base_url', $mergedConfig['cas']['base_url']);
        $container->setParameter('open_demat_admin.cas.logout_url', $mergedConfig['cas']['logout_url']);
        $container->setParameter('open_demat_admin.cas.host', $mergedConfig['cas']['host']);
        $container->setParameter('open_demat_admin.cas.port', $mergedConfig['cas']['port']);
        $container->setParameter('open_demat_admin.cas.path', $mergedConfig['cas']['path']);
        $container->setParameter('open_demat_admin.cas.login_target', $mergedConfig['cas']['login_target']);
        $container->setParameter('open_demat_admin.cas.gateway', $mergedConfig['cas']['gateway']);
        $container->setParameter('open_demat_admin.ldap.enabled', $mergedConfig['ldap']['enabled']);
        $container->setParameter('open_demat_admin.ldap.connection_string', $mergedConfig['ldap']['connection_string']);
        $container->setParameter('open_demat_admin.ldap.base_dn', $mergedConfig['ldap']['base_dn']);
        $container->setParameter('open_demat_admin.ldap.search_filter', $mergedConfig['ldap']['search_filter']);
        $container->setParameter('open_demat_admin.ldap.bind_dn', $mergedConfig['ldap']['bind_dn']);
        $container->setParameter('open_demat_admin.ldap.bind_password', $mergedConfig['ldap']['bind_password']);
        $container->setParameter('open_demat_admin.ldap.identifier_attribute', $mergedConfig['ldap']['identifier_attribute']);
        $container->setParameter('open_demat_admin.ldap.email_attribute', $mergedConfig['ldap']['email_attribute']);
        $container->setParameter('open_demat_admin.ldap.first_name_attribute', $mergedConfig['ldap']['first_name_attribute']);
        $container->setParameter('open_demat_admin.ldap.last_name_attribute', $mergedConfig['ldap']['last_name_attribute']);
        $container->setParameter('open_demat_admin.ldap.auto_create_user', $mergedConfig['ldap']['auto_create_user']);
        $container->setParameter('open_demat_admin.saml2.enabled', $mergedConfig['saml2']['enabled']);
        $container->setParameter('open_demat_admin.saml2.strict', $mergedConfig['saml2']['strict']);
        $container->setParameter('open_demat_admin.saml2.debug', $mergedConfig['saml2']['debug']);
        $container->setParameter('open_demat_admin.saml2.sp_entity_id', $mergedConfig['saml2']['sp_entity_id']);
        $container->setParameter('open_demat_admin.saml2.sp_acs_url', $mergedConfig['saml2']['sp_acs_url']);
        $container->setParameter('open_demat_admin.saml2.sp_sls_url', $mergedConfig['saml2']['sp_sls_url']);
        $container->setParameter('open_demat_admin.saml2.sp_name_id_format', $mergedConfig['saml2']['sp_name_id_format']);
        $container->setParameter('open_demat_admin.saml2.sp_x509_cert', $mergedConfig['saml2']['sp_x509_cert']);
        $container->setParameter('open_demat_admin.saml2.sp_private_key', $mergedConfig['saml2']['sp_private_key']);
        $container->setParameter('open_demat_admin.saml2.idp_metadata_file', $mergedConfig['saml2']['idp_metadata_file']);
        $container->setParameter('open_demat_admin.saml2.idp_metadata_url', $mergedConfig['saml2']['idp_metadata_url']);
        $container->setParameter('open_demat_admin.saml2.idp_metadata_xml', $mergedConfig['saml2']['idp_metadata_xml']);
        $container->setParameter('open_demat_admin.saml2.idp_entity_id', $mergedConfig['saml2']['idp_entity_id']);
        $container->setParameter('open_demat_admin.saml2.idp_sso_url', $mergedConfig['saml2']['idp_sso_url']);
        $container->setParameter('open_demat_admin.saml2.idp_slo_url', $mergedConfig['saml2']['idp_slo_url']);
        $container->setParameter('open_demat_admin.saml2.idp_x509_cert', $mergedConfig['saml2']['idp_x509_cert']);
        $container->setParameter('open_demat_admin.saml2.authn_requests_signed', $mergedConfig['saml2']['authn_requests_signed']);
        $container->setParameter('open_demat_admin.saml2.logout_request_signed', $mergedConfig['saml2']['logout_request_signed']);
        $container->setParameter('open_demat_admin.saml2.logout_response_signed', $mergedConfig['saml2']['logout_response_signed']);
        $container->setParameter('open_demat_admin.saml2.want_messages_signed', $mergedConfig['saml2']['want_messages_signed']);
        $container->setParameter('open_demat_admin.saml2.want_assertions_signed', $mergedConfig['saml2']['want_assertions_signed']);
        $container->setParameter('open_demat_admin.saml2.want_assertions_encrypted', $mergedConfig['saml2']['want_assertions_encrypted']);
        $container->setParameter('open_demat_admin.saml2.want_name_id_encrypted', $mergedConfig['saml2']['want_name_id_encrypted']);
        $container->setParameter('open_demat_admin.saml2.sign_metadata', $mergedConfig['saml2']['sign_metadata']);
        $container->setParameter('open_demat_admin.saml2.identifier_attribute', $mergedConfig['saml2']['identifier_attribute']);
        $container->setParameter('open_demat_admin.saml2.email_attribute', $mergedConfig['saml2']['email_attribute']);
        $container->setParameter('open_demat_admin.saml2.first_name_attribute', $mergedConfig['saml2']['first_name_attribute']);
        $container->setParameter('open_demat_admin.saml2.last_name_attribute', $mergedConfig['saml2']['last_name_attribute']);
        $container->setParameter('open_demat_admin.saml2.auto_create_user', $mergedConfig['saml2']['auto_create_user']);
        $container->setParameter('open_demat_admin.s3.endpoint', $mergedConfig['s3']['endpoint']);
        $container->setParameter('open_demat_admin.s3.region', $mergedConfig['s3']['region']);
        $container->setParameter('open_demat_admin.s3.bucket', $mergedConfig['s3']['bucket']);
        $container->setParameter('open_demat_admin.s3.use_path_style', $mergedConfig['s3']['use_path_style']);
        $container->setParameter('open_demat_admin.s3.access_key', $mergedConfig['s3']['access_key']);
        $container->setParameter('open_demat_admin.s3.secret_key', $mergedConfig['s3']['secret_key']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('services.yaml');
    }
}

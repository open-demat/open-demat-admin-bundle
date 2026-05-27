<?php

namespace OpenDemat\AdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class AdminExtension extends ConfigurableExtension
{
    public function getAlias(): string
    {
        return 'open_demat_admin';
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->setParameter('open_demat_admin.organization.name', $mergedConfig['organization']['name']);
        $container->setParameter('open_demat_admin.organization.logo', $mergedConfig['organization']['logo']);
        $container->setParameter('open_demat_admin.cas.base_url', $mergedConfig['cas']['base_url']);
        $container->setParameter('open_demat_admin.cas.logout_url', $mergedConfig['cas']['logout_url']);
        $container->setParameter('open_demat_admin.cas.host', $mergedConfig['cas']['host']);
        $container->setParameter('open_demat_admin.cas.port', $mergedConfig['cas']['port']);
        $container->setParameter('open_demat_admin.cas.path', $mergedConfig['cas']['path']);
        $container->setParameter('open_demat_admin.cas.login_target', $mergedConfig['cas']['login_target']);
        $container->setParameter('open_demat_admin.cas.gateway', $mergedConfig['cas']['gateway']);
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

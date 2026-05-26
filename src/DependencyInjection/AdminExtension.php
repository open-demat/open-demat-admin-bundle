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

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('services.yaml');
    }
}

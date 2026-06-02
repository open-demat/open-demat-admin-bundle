<?php

namespace OpenDemat\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('open_demat_admin');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('organization')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('Open Demat')->end()
                        ->scalarNode('logo')->defaultValue('assets/img/open-demat-logo.png')->end()
                    ->end()
                ->end()
                ->arrayNode('theme')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('primary_color')->defaultValue('#E30613')->end()
                        ->scalarNode('primary_dark_color')->defaultValue('#15202B')->end()
                    ->end()
                ->end()
                ->arrayNode('cas')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('base_url')->defaultValue('')->end()
                        ->scalarNode('logout_url')->defaultValue('')->end()
                        ->scalarNode('host')->defaultValue('')->end()
                        ->scalarNode('port')->defaultValue('443')->end()
                        ->scalarNode('path')->defaultValue('')->end()
                        ->scalarNode('login_target')->defaultValue('')->end()
                        ->scalarNode('gateway')->defaultValue('0')->end()
                    ->end()
                ->end()
                ->arrayNode('saml2')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->defaultValue('0')->end()
                        ->scalarNode('identifier_attribute')->defaultValue('REMOTE_USER')->end()
                        ->scalarNode('email_attribute')->defaultValue('mail')->end()
                        ->scalarNode('first_name_attribute')->defaultValue('givenName')->end()
                        ->scalarNode('last_name_attribute')->defaultValue('sn')->end()
                        ->scalarNode('default_email_domain')->defaultValue('')->end()
                        ->scalarNode('auto_create_user')->defaultValue('1')->end()
                        ->scalarNode('login_url')->defaultValue('')->end()
                    ->end()
                ->end()
                ->arrayNode('s3')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('endpoint')->defaultValue('')->end()
                        ->scalarNode('region')->defaultValue('')->end()
                        ->scalarNode('bucket')->defaultValue('')->end()
                        ->scalarNode('use_path_style')->defaultValue('1')->end()
                        ->scalarNode('access_key')->defaultValue('')->end()
                        ->scalarNode('secret_key')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

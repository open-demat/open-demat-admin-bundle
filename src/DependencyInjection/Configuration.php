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
                        ->scalarNode('logo')->defaultValue('')->end()
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

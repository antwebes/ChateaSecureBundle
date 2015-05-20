<?php

namespace Ant\Bundle\ChateaSecureBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('chatea_secure');

        $rootNode
            ->children()
                ->arrayNode('app_auth')
                    ->children()
                        ->scalarNode('enviroment')->end()
                        ->scalarNode('client_id')->end()
                        ->scalarNode('secret')->end()
                    ->end()
                ->end()
                ->scalarNode("api_endpoint")->defaultValue('default')->end()
                ->scalarNode("homepage_path")->defaultValue('/')->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}

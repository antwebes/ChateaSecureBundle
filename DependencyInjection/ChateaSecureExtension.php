<?php

namespace Ant\Bundle\ChateaSecureBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ChateaSecureExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('chatea_secure.app_auth.client_id', $config['app_auth']['client_id']);
        $container->setParameter('chatea_secure.app_auth.secret', $config['app_auth']['secret']);
        $container->setParameter('chatea_secure.app_auth.enviroment', $config['app_auth']['enviroment']);
        $container->setParameter('chatea_secure.homepage_path', $config['homepage_path']);
        if($config['api_endpoint'] == 'default'){
            $config['api_endpoint'] = "http://api.chatsfree.net";
        }
        $container->setParameter('chatea_secure.api_endpoint', $config['api_endpoint']);
    }
}

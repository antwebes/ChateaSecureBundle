<?php 
namespace Ant\Bundle\ChateaSecureBundle\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
 
class AutologinSecurityFactory implements SecurityFactoryInterface
{
    public function getKey()
    {
        return 'antwebs_chateasecure_login';
    }
 
    protected function getListenerId()
    {
        return 'security.authentication.listener.autologin';
    }

    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'ecurity.authentication_provider.antwebs_chateasecure.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('security.authentication_provider.antwebs_chateasecure'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'ant_bundle.chateasecurebundle.security.firewall.autologinlistener.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('ant_bundle.chateasecurebundle.security.firewall.autologinlistener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    /**
     * Defines the position at which the provider is called.
     * Possible values: pre_auth, form, http, and remember_me.
     *
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    public function addConfiguration(NodeDefinition $builder)
    {
    }
}
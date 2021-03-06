<?php

namespace Ant\Bundle\ChateaSecureBundle;

use Ant\Bundle\ChateaSecureBundle\DependencyInjection\Factory\AutologinSecurityFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ant\Bundle\ChateaSecureBundle\DependencyInjection\Factory\SecurityFactory;

class ChateaSecureBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SecurityFactory());
        $extension->addSecurityListenerFactory(new AutologinSecurityFactory());
    }
}

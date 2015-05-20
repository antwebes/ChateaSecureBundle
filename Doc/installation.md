Installation
------------

Add do your ```composer.yml``` file the following ```"antwebes/chatea-secure-bundle": "dev-master"``` line and then execute on your terninal:

```$ composer install```

In the ```app/AppKernel.php``` register the bundle:

```
<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    
    public function registerBundles()
    {
        $bundles = array(
            //...
            new Ant\Bundle\ChateaSecureBundle\ChateaSecureBundle(),
            //...
        );
        
        //...

        return $bundles;
    }
}
```

Making remember me work
=======================

In order to make the remember me able to work you have to change the remember me manager class of the security manager of symfony in the ```security.authentication.rememberme.services.simplehash.class``` like bellow:

```
parameters:
    security.authentication.rememberme.services.simplehash.class: Ant\Bundle\ChateaSecureBundle\Security\Http\RememberMe\AccessTokenBasedRememberMeService
```

<?php
namespace Ant\Bundle\ChateaSecureBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;

interface ChateaUserProviderInterface extends UserProviderInterface
{
    public function loadUser($username, $password);
}
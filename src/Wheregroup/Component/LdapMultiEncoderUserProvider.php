<?php

namespace Wheregroup\Component;
/**
 *
 * @author David Patzke <david.patzke@wheregroup.com>
 */



use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\LdapUserProvider;
/**
 * LdapUserProvider is a simple user provider on top of ldap.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class LdapMultiEncoderUserProvider extends LdapUserProvider
{
    private $ldap;
    private $baseDn;
    private $searchDn;
    private $searchPassword;
    private $defaultRoles;
    private $defaultSearch;



    public function loadUser($username, $user)
    {
        $password = isset($user['userpassword']) ? $user['userpassword'] : null;

        $roles = $this->defaultRoles;
        $password = $password[0];

        return new LdapUser($username, $password, $roles);
    }


}

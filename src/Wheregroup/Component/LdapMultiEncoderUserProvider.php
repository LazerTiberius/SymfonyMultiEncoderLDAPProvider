<?php

namespace Wheregroup\Component;



use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\LdapUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Class LdapMultiEncoderUserProvider
 *
 * @package Wheregroup\Component
 * @author  David Patzke <david.patzke@wheregroup.com>
 */
class LdapMultiEncoderUserProvider extends LdapUserProvider
{
    private $ldap;
    private $baseDn;
    private $searchDn;
    private $searchPassword;
    private $defaultRoles;
    private $defaultSearch;
    private $groupSearchFilter;
    private $groupBaseDN;
    private $group_uid_key;

    /**
     * LdapMultiEncoderUserProvider constructor.
     *
     * @param LdapClientInterface $ldap
     * @param string              $baseDn
     * @param null                $searchDn
     * @param null                $searchPassword
     * @param array               $defaultRoles
     * @param string              $uidKey
     * @param string              $filter
     * @param string              $groupBaseDN
     * @param string              $groupSearchFilter
     * @param string              $group_uid_key
     */
    public function __construct(LdapClientInterface $ldap, $baseDn, $searchDn = null, $searchPassword = null, array $defaultRoles = array(), $uidKey = 'sAMAccountName', $filter = '({uid_key}={username})', $groupBaseDN, $groupSearchFilter, $group_uid_key)
    {
        parent::__construct($ldap, $baseDn, $searchDn, $searchPassword, $defaultRoles, $uidKey, $filter);
        $this->ldap              = $ldap;
        $this->baseDn            = $baseDn;
        $this->searchDn          = $searchDn;
        $this->searchPassword    = $searchPassword;
        $this->defaultRoles      = $defaultRoles;
        $this->defaultSearch     = str_replace('{uid_key}', $uidKey, $filter);
        $this->groupBaseDN       = $groupBaseDN;
        $this->groupSearchFilter = $groupSearchFilter;

        $this->group_uid_key = $group_uid_key;
    }

    /**
     * @param $username
     * @param $user
     * @return LdapUser
     */
    public function loadUser($username, $user)
    {
        $password = isset($user['userpassword']) ? $user['userpassword'] : null;

        $password = $password[0];


        $roles = $this->defaultRoles;
        $ldapRoles = $this->getLdapUserRoles($username);
        foreach ($ldapRoles as $role) {
            if (!empty($role['cn'][0])) {

                $roles[] = 'ROLE_' . $role[$this->group_uid_key][0];
            }
        }


        return new LdapUser($username, $password, $roles);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return new LdapUser($user->getUsername(), null, $user->getRoles());
    }

    /**
     * @param $username
     * @return array
     */
    protected function getLdapUserRoles($username){

        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $username = $this->ldap->escape($username, '', LDAP_ESCAPE_FILTER);
            $query    = str_replace('{username}', $username, $this->groupSearchFilter);
            $search = $this->ldap->find($this->groupBaseDN, $query);

        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('Users "%s" groups could not be fetched from LDAP.', $username), 0, $e);
        }


        if($search === null) {
            $search = [];
        }

        return $search;



    }


}

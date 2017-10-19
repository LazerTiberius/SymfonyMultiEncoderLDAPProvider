<?php
/**
 *
 * @author Andriy Oblivantsev <eslider@gmail.com>
 */

namespace Wheregroup\Component;

use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @property  username
 * @property string encoder
 * @property  password
 */
class LdapUserMultiEncoder  implements AdvancedUserInterface, EncoderAwareInterface
{
    private $username;
    private $password;
    private $enabled;
    private $accountNonExpired;
    private $credentialsNonExpired;
    private $accountNonLocked;
    private $roles;
    private $pwEncoder;

    /**
     * LdapUser constructor.
     *
     * @param       $username
     * @param       $password
     * @param array $roles
     * @param bool  $enabled
     * @param bool  $userNonExpired
     * @param bool  $credentialsNonExpired
     * @param bool  $userNonLocked
     */
    public function __construct($username, $password, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        if ('' === $username || null === $username) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        preg_match_all('/(\{)(.*)(\})(.*)/', $password, $ldapPw  );

        if (count($ldapPw) == 5) {
            $this->password = $ldapPw[4][0];
            $this->pwEncoder  = $ldapPw[2][0];
        } else {
            $this->password = $password;
            $this->pwEncoder  = 'plaintext';
        }


        $this->username = $username;

        $this->enabled = $enabled;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->accountNonLocked = $userNonLocked;
        $this->roles = $roles;
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * Gets the name of the encoder used to encode the password.
     *
     * If the method returns null, the standard way to retrieve the encoder
     * will be used instead.
     *
     * @return string
     */
    public function getEncoderName()
    {
        return $this->pwEncoder;
    }
}
<?php

namespace SpyimmoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="access_token")
 */
class AccessToken implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="oauth2_owner")
     */
    private $oAuth2Owner;

    /**
     * @ORM\Column(type="string", name="oauth2_user_id")
     */
    private $oAuth2UserId;

    /**
     * @ORM\Column(type="string", name="access_token")
     */
    private $accessToken;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    private $createadAt;

    /**
     * @ORM\ManyToOne(targetEntity="SpyimmoBundle\Entity\Profile")
     */
    private $profile;

    public function __construct(Profile $profile, $oAuth2Owner, $oAuth2UserId, $accessToken)
    {
        $this->profile = $profile;
        $this->oAuth2Owner = $oAuth2Owner;
        $this->oAuth2UserId = $oAuth2UserId;
        $this->accessToken = $accessToken;

        $this->createadAt = new \DateTime();
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getOAuth2Owner()
    {
        return $this->oAuth2Owner;
    }

    public function getOAuth2UserId()
    {
        return $this->oAuth2UserId;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getCreatedAt()
    {
        return $this->createadAt;
    }

    public function getUsername()
    {
        return $this->accessToken;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getPassword()
    {
        return;
    }

    public function getSalt()
    {
        return;
    }

    public function eraseCredentials()
    {
        return;
    }
}

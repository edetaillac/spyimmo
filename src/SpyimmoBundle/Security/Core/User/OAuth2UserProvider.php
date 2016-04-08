<?php

namespace SpyimmoBundle\Security\Core\User;


use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use SpyimmoBundle\Entity\AccessToken;
use SpyimmoBundle\Entity\Profile;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class OAuth2UserProvider extends OAuthUserProvider
{
    protected $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function loadUserByUsername($accessToken)
    {
        $accessToken = $this->registry
            ->getRepository("SpyimmoBundle:AccessToken")
            ->findOneByAccessToken($accessToken)
        ;

        if (null === $accessToken) {
            throw new UsernameNotFoundException(sprintf('AccessToken "%s" not found.', $accessToken));
        }

        return $accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $email = $response->getEmail();
        $owner = $response->getResourceOwner()->getName();
        $username = $response->getUsername();

        $accessToken = $this->registry
            ->getRepository("SpyimmoBundle:AccessToken")
            ->findOneBy(['oAuth2Owner' => $owner, 'oAuth2UserId' => $username])
        ;

        $em = $this->registry->getManager();

        if (null === $accessToken) {
            $profile = $this->registry
                ->getRepository("SpyimmoBundle:Profile")
                ->findOneByEmail($email)
            ;

            if (null === $profile) {
                $profile = new Profile($email, $response->getRealname());
                $em->persist($profile);
            }

            $accessToken = new AccessToken($profile, $owner, $username, $response->getAccessToken());
            $em->persist($accessToken);
        }

        $em->flush();

        return $accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'SpyimmoBundle\\Entity\\AccessToken';
    }
}

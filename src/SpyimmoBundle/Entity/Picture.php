<?php

namespace SpyimmoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pictures")
 * @ORM\HasLifecycleCallbacks
 */
class Picture
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SpyimmoBundle\Entity\Offer", inversedBy="pictures")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $offer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $src;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Offer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     */
    public function setOffer(Offer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * @return mixed
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * @param mixed $src
     */
    public function setSrc($src)
    {
        $this->src = $src;
    }

}
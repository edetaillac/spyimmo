<?php

namespace SpyimmoBundle\Manager;

use Doctrine\ORM\EntityManager;
use SpyimmoBundle\Entity\Offer;
use SpyimmoBundle\Entity\Picture;
use SpyimmoBundle\Logger\SpyimmoLogger;
use SpyimmoBundle\Repository\OfferRepository;

class OfferManager
{
    protected $em;
    protected $offerRepository;
    protected $postalCodes;

    /** @var  SpyimmoLogger */
    protected $spyimmoLogger;

    public function __construct(EntityManager $em, OfferRepository $offerRepository, SpyimmoLogger $spyimmoLogger, $postalCodes)
    {
        $this->em = $em;
        $this->offerRepository = $offerRepository;
        $this->spyimmoLogger = $spyimmoLogger;
        $this->postalCodes = $postalCodes;
    }

    public function isNew($url)
    {
        return !$this->offerRepository->findOneByUrl($url);
    }

    public function createOffer($title, $description, array $images, $url, $name, $price = null, $postalCode = null, $surface = null, $tel = null, $isBlacklisted = false)
    {
        if(!$description && !$tel && !$price && count($images) == 0) {
            $this->spyimmoLogger->logInfo(sprintf('   <comment>Not enough information to evaluate offer</comment>', $url));
            return false;
        }

        $info = $this->cleanString($title . ' ' . $description);
        if(!$isBlacklisted) {
            $isBlacklisted = $this->isBlacklisted($info);
        }

        $url = $this->cleanUrl($url);
        $title = $this->cleanString($title);
        $description = $this->cleanString($description);
        $isSuspicious = $this->isSuspicious($description);

        $offer = new Offer();
        $offer->setTitle($title);
        $offer->setDescription($description);
        $offer->setUrl($url);
        $offer->setLabel($name);
        $offer->setSuspicious($isSuspicious);

        if ($tel) {
            $offer->setTel($this->cleanTel($tel));
        }

        if ($price) {
            $offer->setPrice($this->formatPrice($price));
        } elseif (($price = $this->extractPrice($info)) > 0) {
            $offer->setPrice($price);
        }

        if ($postalCode) {
            $offer->setLocation($postalCode);
        } elseif (($postalCode = $this->extractPostalCode($info)) > 0) {
            $offer->setLocation($postalCode);
        }
        $isHidden = $this->isHidden($postalCode) || $isBlacklisted || $isSuspicious;
        $offer->setHidden($isHidden);

        if ($surface) {
            $offer->setSurface(intval($surface));
        } elseif (($surface = $this->extractSurface($info)) > 0) {
            $offer->setSurface($surface);
        }

        foreach($images as $image){
            $picture = new Picture();
            $picture->setSrc($image);
            $picture->setOffer($offer);
            $this->em->persist($picture);
        }

        $this->em->persist($offer);
        $this->em->flush();

        if (!$isHidden) {
            return true;
        }

        if($isSuspicious) {
            $this->spyimmoLogger->logInfo(sprintf('   <comment>Suspicious</comment>', $url));
        } elseif($isBlacklisted) {
            $this->spyimmoLogger->logInfo(sprintf('   <comment>Blacklisted</comment>', $url));
        } elseif($isHidden) {
            $this->spyimmoLogger->logInfo(sprintf('   <comment>Hidden</comment>', $url));
        }

        return false;
    }

    public function cleanUrl($string)
    {
        $string = preg_replace('/(#.*)$/', '', $string);
        $string = preg_replace('/(\?.*)$/', '', $string);

        return trim($string);
    }

    protected function cleanString($string)
    {
        $string = preg_replace('/<br\s*\/?>/', ' ', $string);
        $string = preg_replace('/\-/', ' ', $string);
        $string = preg_replace('/\t/', ' ', $string);
        $string = html_entity_decode($string);
        $string = strip_tags($string);
        $string = preg_replace('/\s{2,}/', ' ', $string);

        return trim($string);
    }

    protected function cleanTel($string)
    {
        $string = preg_replace('/\-/', '', $string);
        $string = html_entity_decode($string);
        $string = strip_tags($string);
        $string = preg_replace('/\s+/', '', $string);

        return trim($string);
    }

    protected function formatPrice($price)
    {
        $price = $this->cleanString($price);
        $price = html_entity_decode($price);
        $price = preg_replace('/\s/', '', $price);

        return intval($price);
    }

    protected function extractSurface($desc)
    {
        preg_match('/([0-9\.,]+)\s*m[²2]{1}/i', $desc, $result);
        if (isset($result[1])) {
            return intval($result[1]);
        }

        return 0;
    }

    protected function extractPostalCode($desc)
    {
        preg_match('/(75[0-9]{3})\W/i', $desc, $result);
        if (isset($result[1])) {
            return intval($result[1]);
        }

        preg_match('/paris ([0-9]{1,2})[\D]{1}/i', $desc, $result);
        if (isset($result[1])) {
            return 75000 + $result[1];
        }

        return 0;
    }

    protected function extractPrice($desc)
    {
        preg_match('/([0-9\.,]+)\s*€/mi', $desc, $result);
        if (isset($result[1])) {
            return $this->formatPrice($result[1]);
        }

        preg_match('/([0-9\.,]+)\s*euro/mi', $desc, $result);
        if (isset($result[1])) {
            return $this->formatPrice($result[1]);
        }

        return 0;
    }

    protected function isBlacklisted($desc)
    {
        $regexps = array(
            '/colloc|coloc/mi',
            '/sous\Wlocation/mi',
            '/sous\Wloue/mi',
            '/chambre [d\’\']*[Éée]{1}tudiant/mi',
        );

        foreach ($regexps as $regexp) {
            if (preg_match($regexp, $desc)) {
                $this->spyimmoLogger->logInfo(sprintf('   <comment>STOP Word detected - "%s" found</comment>', $regexp));
                return 1;
            }
        }

        if (preg_match('/\Wmeubl[éÉ]/mi', $desc) && !preg_match('/\Wnon\W+meubl[éÉ]/mi', $desc)) {
            $this->spyimmoLogger->logInfo(sprintf('   <comment>STOP Word detected - "%s" found and "%s" not found</comment>', '/\Wmeubl[éÉ]/mi', '/\Wnon\W+meubl[éÉ]/mi'));
            return 1;
        }


        return 0;
    }

    protected function isSuspicious($description)
    {
        $isSuspicious = $this->offerRepository->getSimilarOffer($description);

        return (count($isSuspicious) > 0) ? 1 : 0;
    }

    protected function isHidden($postalCode) {
        if(count($this->postalCodes) > 0 && $postalCode > 0 && !in_array($postalCode, $this->postalCodes)) {
            return 1;
        }

        return 0;
    }

    /**
     * @return SpyimmoLogger
     */
    public function getSpyimmoLogger()
    {
        return $this->spyimmoLogger;
    }

    /**
     * @param SpyimmoLogger $spyimmoLogger
     */
    public function setSpyimmoLogger($spyimmoLogger)
    {
        $this->spyimmoLogger = $spyimmoLogger;
    }


}

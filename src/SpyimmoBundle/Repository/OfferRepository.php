<?php

namespace SpyimmoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SpyimmoBundle\Entity\Offer;

class OfferRepository extends EntityRepository
{

    public function getFavoriteOffers()
    {
        $query = $this->createQueryBuilder('o')
          ->leftJoin('o.pictures', 'p')
          ->addSelect('p')
          ->where('o.favorite = :favorite')
          ->andWhere('o.hidden = :hidden')
          ->setParameter('favorite', 1)
          ->setParameter('hidden', 0)
          ->orderBy('o.created', 'DESC')
          ->setMaxResults(150);

        return $query->getQuery()->useResultCache(true, 300)->getResult();
    }

    public function getHiddenOffers()
    {
        $query = $this->createQueryBuilder('o')
          ->leftJoin('o.pictures', 'p')
          ->addSelect('p')
          ->where('o.hidden = :hidden')
          ->orWhere('o.suspicious = :suspicious')
          ->setParameter('hidden', 1)
          ->setParameter('suspicious', 1)
          ->orderBy('o.created', 'DESC')
          ->setMaxResults(150);

        return $query->getQuery()->useResultCache(true, 300)->getResult();
    }

    public function getOffers()
    {
        $query = $this->createQueryBuilder('o')
          ->leftJoin('o.pictures', 'p')
          ->addSelect('p')
          ->where('o.hidden = :hidden')
          ->andWhere('o.suspicious = :suspicious')
          ->setParameter('hidden', 0)
          ->setParameter('suspicious', 0)
          ->orderBy('o.created', 'DESC')
          ->setMaxResults(150);

        return $query->getQuery()->useResultCache(true, 300)->getResult();
    }

    public function getOffer($id)
    {
        $query = $this->createQueryBuilder('o')
            ->leftJoin('o.pictures', 'p')
            ->addSelect('p')
            ->where('o.id = :id')
            ->setParameter('id', $id);

        return $query->getQuery()->useResultCache(true, 600)->getSingleResult();
    }

    public function getSimilarOffer($description)
    {
        $description = substr($description, 35, 70);

        $query = $this->createQueryBuilder('o')
          ->where('o.description like :description')
          ->setParameter('description', "%$description%");

        return $query->getQuery()->getResult();
    }

    public function toggleFavorite($id, $value)
    {
        $qB = $this->getEntityManager()->createQueryBuilder();
        $qB->update('SpyimmoBundle:Offer', 'o')
          ->set('o.favorite', ':value')
          ->where('o.id = :id')
          ->setParameter('value', $value)
          ->setParameter('id', $id);

        return $qB->getQuery()->execute();
    }

    public function toggleContacted($id, $value)
    {
        $qB = $this->getEntityManager()->createQueryBuilder();
        $qB->update('SpyimmoBundle:Offer', 'o')
            ->set('o.contacted', ':value')
            ->set('o.contactedAt', ':contactDate')
            ->where('o.id = :id')
            ->setParameter('value', $value)
            ->setParameter('contactDate', new \DateTime("now"))
            ->setParameter('id', $id);

        return $qB->getQuery()->execute();
    }

    public function toggleHidden($id, $value)
    {
        $qB = $this->getEntityManager()->createQueryBuilder();
        $qB->update('SpyimmoBundle:Offer', 'o')
          ->set('o.hidden', ':value')
          ->where('o.id = :id')
          ->setParameter('value', $value)
          ->setParameter('id', $id);

        return $qB->getQuery()->execute();
    }
}

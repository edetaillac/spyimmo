<?php

namespace SpyimmoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need

        $repository = $this->get('offer.repository');
        $offers = $repository->getOffers();

        return $this->render('@Spyimmo/Default/index.html.twig', array('offers' => $offers, 'title' => 'Dernières offres'));
    }

    /**
     * @Route("/detail/{id}", name="detail", options={"expose"=true})
     */
    public function detailAction($id)
    {
        $repository = $this->get('offer.repository');
        $offer = $repository->getOffer($id);
        if(!$offer->getViewed()) {
            $repository->markAsViewed($id);
        }

        return $this->render('@Spyimmo/Default/offer.html.twig', array('offer' => $offer));
    }

    /**
     * @Route("/favorite/all", name="indexFavorite")
     */
    public function indexFavoriteAction(Request $request)
    {
        $repository = $this->get('offer.repository');
        $offers = $repository->getFavoriteOffers();

        return $this->render('@Spyimmo/Default/index.html.twig', array('offers' => $offers, 'title' => 'Offres favorites'));
    }

    /**
     * @Route("/hidden/all", name="indexHidden")
     */
    public function indexHiddenAction(Request $request)
    {
        $repository = $this->get('offer.repository');
        $offers = $repository->getHiddenOffers();

        return $this->render('@Spyimmo/Default/index.html.twig', array('offers' => $offers, 'title' => 'Offres cachées'));
    }

    /**
     * @Route("/favorite/{id}", name="favorite", options={"expose"=true})
     */
    public function favoriteAction($id)
    {
        $repository = $this->get('offer.repository');
        $repository->toggleFavorite($id, 1);

        return new Response('ok');
    }

    /**
     * @Route("/contacted/{id}", name="contacted", options={"expose"=true})
     */
    public function contactedAction($id)
    {
        $repository = $this->get('offer.repository');
        $repository->toggleContacted($id, 1);

        return new Response('ok');
    }

    /**
     * @Route("/unfavorite/{id}", name="unfavorite", options={"expose"=true})
     */
    public function unfavoriteAction($id)
    {
        $repository = $this->get('offer.repository');
        $repository->toggleFavorite($id, 0);

        return new Response('ok');
    }

    /**
     * @Route("/hide/{id}", name="hide", options={"expose"=true})
     */
    public function hideAction($id)
    {
        $repository = $this->get('offer.repository');
        $repository->toggleHidden($id, 1);

        return new Response('ok');
    }

    /**
     * @Route("/unhide/{id}", name="unhide", options={"expose"=true})
     */
    public function unhideAction($id)
    {
        $repository = $this->get('offer.repository');
        $repository->toggleHidden($id, 0);

        return new Response('ok');
    }
}

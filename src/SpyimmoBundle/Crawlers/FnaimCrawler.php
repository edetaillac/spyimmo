<?php

namespace SpyimmoBundle\Crawlers;

use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use SpyimmoBundle\Services\CrawlerService;

/**
 * Class FnaimCrawler
 *
 */
class FnaimCrawler extends AbstractCrawler
{
    const NAME = 'Fnaim';
    const SITE_URL = 'http://www.fnaim.fr';

    const SEARCH_URL = 'http://www.fnaim.fr/18-louer.htm?TRANSACTION=2&localites=[{"id":75,"type":2,"label":"PARIS (75)"}]&TYPE[]=1&TYPE[]=2&NB_PIECES[]=%s&SURFACE[]=%s&SURFACE[]=&SURFACE_TERRAIN[]=&SURFACE_TERRAIN[]=&PRIX[]=&PRIX[]=%s&submit=Valider&idtf=18&op=&cp=d41d8cd98f00b204e980&mp=20';

    public function __construct()
    {
        parent::__construct();
        $this->name = self::NAME;
        $this->searchUrl = self::SEARCH_URL;
    }

    public function getOffers($criterias, $excludedCrawlers = array())
    {
        $this->searchUrl = $this->transformFnaimUrl();
        $this->searchUrl = $this->generateUrl($this->searchUrl, $criterias);
        parent::getOffers($criterias, $excludedCrawlers);

        $offers = $this->nodeFilter($this->crawler, '.annonce_liste ul li h3 a');
        if ($offers) {
            $offers->each(
              function (Crawler $node) {

                  $title = $node ? $node->text() : '';
                  $url = self::SITE_URL . trim($node->attr('href'));

                  $isNew = $this->getOfferDetail($url, $title);
                  $this->cptNew += ($isNew) ? 1 : 0;
                  ++$this->cpt;

              }
            );
        }

        return $this->cptNew;
    }

    protected function getOfferDetail($url, $title)
    {
        $isNew = parent::getOfferDetail($url, $title);

        if(!$isNew) {
            return 0;
        }

        $fullTitle = $this->nodeFilter($this->crawler, '#contenu .annonce_fiche h1', $url);
        $title = $fullTitle ? $fullTitle->text() : $title;

        $description = $this->nodeFilter($this->crawler, '#contenu .annonce_fiche .description p', $url);
        $description = $description ? $description->text() : '';

        $descriptionBisBlock = $this->nodeFilter($this->crawler, '#contenu .annonce_fiche .caracteristique li');
        $descriptionBis = $this->nodeFilter($descriptionBisBlock->first(), 'li');
        if ($descriptionBis) {
            $descriptionBis->each(
              function (Crawler $node) use (&$description) {
                  $description .= ' ' . $node->text();
              }
            );
        }

        $images = [];
        $imageNodes = $this->nodeFilter($this->crawler, '#contenu .annonce_fiche #diapo_annonce .carouselSync img', $url);
        if($imageNodes) {
            $imageNodes->each(
                function (Crawler $node) use (&$images) {
                    $images[] = $node->attr('src');
                }
            );
        }

        $price = $this->nodeFilter($this->crawler, '#contenu .annonce_fiche .entete h3 span', $url);
        $price = $price ? $price->text() : '';

        $tel = $this->nodeFilter($this->crawler, '#contenu .actions #agence_call', $url);
        $tel = $tel ? $tel->text() : '';

        return $this->offerManager->createOffer($title, $description, $images, $url, self::NAME, $price, null, null, $tel);
    }

    protected function transformFnaimUrl()
    {
        return sprintf(self::SEARCH_URL, CrawlerService::MIN_NB_ROOM, CrawlerService::MIN_SURFACE, CrawlerService::MAX_BUDGET);
    }

}

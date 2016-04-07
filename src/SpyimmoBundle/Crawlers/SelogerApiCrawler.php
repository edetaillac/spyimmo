<?php

namespace SpyimmoBundle\Crawlers;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use SpyimmoBundle\Services\CrawlerService;

/**
 * Class SelogerApiCrawler
 *
 */
class SelogerApiCrawler extends SelogerCrawler
{
    const NAME = 'SeLogerApi';

    const SEARCH_URL = 'http://ws.seloger.com/search.xml?idtt=1&idtypebien=1,9&cp=75&tri=initial&si_meuble=0';

    public function __construct()
    {
        parent::__construct();

        $this->name = self::NAME;
        $this->searchUrl = self::SEARCH_URL;
    }

    public function getOffers($criterias, $excludedCrawlers = array())
    {
        parent::getOffers($criterias, $excludedCrawlers);

        $offers = $this->nodeFilter($this->crawler, 'annonces > annonce');

        if ($offers) {
            $offers->each(
              function (Crawler $node) {
                  $title = $this->nodeFilter($node, 'titre')->text();

                  if ($title != '') {
                      $url = $this->nodeFilter($node, 'permaLien');
                      $url = $url ? trim($url->text()) : '';
                      $isNew = $this->getOfferDetail($url, $title);
                      $this->cptNew += ($isNew) ? 1 : 0;
                  }
                  ++$this->cpt;
              }
            );
        }

        return $this->cptNew;
    }
}

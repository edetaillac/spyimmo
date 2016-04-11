<?php

namespace SpyimmoBundle\Crawlers;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use SpyimmoBundle\Services\CrawlerService;

/**
 * Class SelogerCrawler
 *
 */
class SelogerCrawler extends AbstractCrawler
{
    const NAME = 'SeLoger';

    const SEARCH_URL = 'http://www.seloger.com/list.htm?idtt=1&idtypebien=1,9&cp=75&tri=d_dt_crea&si_meuble=0';

    public function __construct()
    {
        parent::__construct();

        $this->name = self::NAME;
        $this->searchUrl = self::SEARCH_URL;

        $this->searchCriterias = array(
          CrawlerService::MIN_BUDGET     => 'pxmin',
          CrawlerService::MAX_BUDGET     => 'pxmax',
          CrawlerService::MIN_SURFACE    => 'surfacemin',
          CrawlerService::MAX_SURFACE    => 'surfacemax',
          CrawlerService::MIN_NB_BEDROOM => 'nb_chambresmin',
          CrawlerService::MIN_NB_ROOM    => 'nb_piecesmin',
          CrawlerService::MAX_NB_ROOM    => 'nb_piecesmax',
        );
    }

    public function getOffers($criterias, $excludedCrawlers = array())
    {
        parent::getOffers($criterias, $excludedCrawlers);

        $offers = $this->nodeFilter($this->crawler, '.listing_infos h2 a');

        if ($offers) {
            $offers->each(
              function (Crawler $node) {

                  $title = $node ? $node->text() : '';
                  if ($title != '') {
                      $url = trim($node->attr('href'));
                      $isNew = $this->getOfferDetail($url, $title);
                      $this->cptNew += ($isNew) ? 1 : 0;
                  }
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

        $fullTitle = $this->nodeFilter($this->crawler, 'h1.detail-title', $url);
        $title = $fullTitle ? $fullTitle->text() : $title;

        $description = $this->nodeFilter($this->crawler, '.detail__description .description', $url);
        $description = $description ? $description->text() : '';

        $descriptionBis = $this->nodeFilter($this->crawler, '.detail__description .description-liste li', $url);
        if ($descriptionBis) {
            $descriptionBis->each(
              function (Crawler $node) use (&$description) {
                  $description .= ' ' . $node->text();
              }
            );
        }

        $images = [];
        $imageNodes = $this->nodeFilter($this->crawler, '.carrousel_image img', $url);
        if($imageNodes) {
            $imageNodes->each(
                function (Crawler $node) use (&$images) {
                    $images[] = $node->attr('src');
                }
            );
        }

        $price = $this->nodeFilter($this->crawler, '#price', $url);
        $price = $price ? $price->text() : '';

        $tel = $this->nodeFilter($this->crawler, '.detail__description .action__detail-tel span', $url);
        $tel = $tel ? $tel->text() : '';

        return $this->offerManager->createOffer($title, $description, $images, $url, self::NAME, $price, null, null, $tel);
    }
}

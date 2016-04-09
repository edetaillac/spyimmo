<?php

namespace SpyimmoBundle\Crawlers;

use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use SpyimmoBundle\Services\CrawlerService;

/**
 * Class ExplorimoCrawler
 *
 */
class ExplorimoCrawler extends AbstractCrawler
{

    const NAME = 'Explorimo';
    const SITE_URL = 'http://www.explorimmo.com';

    const SEARCH_URL = 'http://www.explorimmo.com/resultat/annonces.html?transaction=location&type=Maison&type=Villa&type=Ferme&type=Moulin&type=Chalet&type=Appartement&type=Chambre&location=paris';

    public function __construct()
    {
        parent::__construct();

        $this->name = self::NAME;
        $this->searchUrl = self::SEARCH_URL;

        $this->searchCriterias = array(
          CrawlerService::MIN_BUDGET  => 'priceMin',
          CrawlerService::MAX_BUDGET  => 'priceMax',
          CrawlerService::MIN_SURFACE => 'areaMin',
          CrawlerService::MAX_SURFACE => 'areaMax',
          CrawlerService::MIN_NB_ROOM => 'roomMin',
          CrawlerService::MAX_NB_ROOM => 'roomMax',
        );
    }

    public function getOffers($criterias, $excludedCrawlers = array())
    {
        parent::getOffers($criterias, $excludedCrawlers);

        $offers = $this->nodeFilter($this->crawler, '#vue .bloc-item-header a');
        if ($offers) {
            $offers->each(
              function (Crawler $node) {

                  $title = $node ? $node->text() : '';

                  if ($title != '') {
                      $url = self::SITE_URL . trim($node->attr('href'));

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

        try {
            $this->crawler = $this->client->request('GET', $url);

            $fullTitle = $this->nodeFilter($this->crawler, '.container-main-informations h1', $url);
            $title = $fullTitle ? $fullTitle->text() : $title;

            $description = $this->nodeFilter($this->crawler, '.container-main .description', $url);
            $description = $description ? $description->text() : '';

            $descriptionBis = $this->nodeFilter($this->crawler, '.container-main .features', $url);
            $description .= $descriptionBis ? $descriptionBis->text() : '';

            $images = [];
            $imageNodes = $this->nodeFilter($this->crawler, '.slides li a img', $url);
            if($imageNodes) {
                $imageNodes->each(
                    function (Crawler $node) use (&$images) {
                        $images[] = $node->attr('src');
                    }
                );
            }

            $price = $this->nodeFilter($this->crawler, '.container-main-informations .price', $url);
            $price = $price ? $price->text() : '';

            // get Contact Phone Number via API
            $tel = $this->fetchTel($url);

            return $this->offerManager->createOffer($title, $description, $images, $url, self::NAME, $price, null, null, $tel);
        } catch (\InvalidArgumentException $e) {
            echo sprintf("[%s] unable to parse %s: %s\n", self::NAME, $url, $e->getMessage());
        }

        return 0;
    }

    protected function fetchTel($url)
    {
        $tel = null;
        $offerId = $this->nodeFilter($this->crawler, '.informations-agency .contact-phone-button', $url);
        $offerId = $offerId ? $offerId->attr('data-classified-id') : '';
        if ($offerId) {
            $response = $this->client->getClient()->get(
                sprintf('http://www.explorimmo.com/rest/classifieds/%s/phone', $offerId)
            );
            $result = json_decode($response->getBody());
            if(property_exists($result, 'phoneNumber') ) {
                $tel = $result->phoneNumber;
            }
        }

        return $tel;
    }
}

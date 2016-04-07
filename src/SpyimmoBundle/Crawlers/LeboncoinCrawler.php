<?php

namespace SpyimmoBundle\Crawlers;

use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use SpyimmoBundle\Services\CrawlerService;

/**
 * Class LeboncoinCrawler
 *
 */
class LeboncoinCrawler extends AbstractCrawler
{

    const NAME = 'LeBonCoin';

    const SEARCH_URL = 'http://www.leboncoin.fr/locations/offres/ile_de_france/paris/?th=1&furn=2';

    public function __construct()
    {
        parent::__construct();

        $this->name = self::NAME;
        $this->searchUrl = self::SEARCH_URL;

        $this->searchCriterias = array(
          CrawlerService::MIN_BUDGET  => 'mrs',
          CrawlerService::MAX_BUDGET  => 'mre',
          CrawlerService::MIN_SURFACE => 'sqs',
          CrawlerService::MAX_SURFACE => 'sqe',
          CrawlerService::MIN_NB_ROOM => 'ros',
          CrawlerService::MAX_NB_ROOM => 'roe',
        );

        $sizeClosure = function ($surf) {
            $valueSize = array(
              0,
              20,
              25,
              30,
              35,
              40,
              50,
              60,
              70,
              80,
              90,
              100,
              110,
              120,
              150,
              300
            );
            foreach ($valueSize as $key => $val) {
                if ($surf <= $val) {
                    return $key;
                }
            }

            return count($valueSize);
        };

        $this->criteriaClosures = array(
          'sqs' => $sizeClosure,
          'sqe' => $sizeClosure
        );
    }

    public function getOffers($criterias, $excludedCrawlers = array())
    {
        parent::getOffers($criterias, $excludedCrawlers);

        $offers = $this->nodeFilter($this->crawler, '.mainList .list_item');
        if ($offers) {
            $offers->each(
              function (Crawler $node) {

                  $title = $this->nodeFilter($node, '.item_title');
                  $title = $title ? $title->text() : '';
                  if ($title != '') {
                      $url = 'http:' . trim($node->attr('href'));
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

            $description = $this->nodeFilter($this->crawler, '.properties_description .value', $url);
            $description = $description ? $description->text() : '';

            $descriptionBis = $this->nodeFilter($this->crawler, '.properties .line:not(.properties_description)', $url);
            if ($descriptionBis) {
                $descriptionBis->each(
                  function (Crawler $node) use (&$description) {
                      $description .= ' ' . $node->text();
                  }
                );
            }
            $description = $this->removeDescriptionInfo($description);

            $image = $this->nodeFilter($this->crawler, '.item_image img', $url);
            $image = $image && (count($image) > 0) ? 'http:' . $image->first()->attr('src') : null;

            $price = $this->nodeFilter($this->crawler, '.item_price .value', $url);
            $price = $price ? $price->text() : '';

            $tel = $this->nodeFilter($this->crawler, '.sidebar .phone_number a', $url);
            $tel = $tel ? $tel->text() : '';

            return $this->offerManager->createOffer($title, $description, $image, $url, self::NAME, $price, null, null, $tel);
        } catch (\InvalidArgumentException $e) {
            echo sprintf("[%s] unable to parse %s: %s\n", self::NAME, $url, $e->getMessage());
        }

        return 0;
    }

    protected function removeDescriptionInfo($description)
    {
        // remove meublé/non meublé legend
        $leBonCoinRegexp = '/(meubl[éÉ]\W*\/\W*non\W+meubl[éÉ]\W+)/mi';
        return preg_replace($leBonCoinRegexp, '', $description);
    }
}

<?php

namespace SpyimmoBundle\Crawlers;

use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use SpyimmoBundle\Services\CrawlerService;

/**
 * Class GdcCrawler
 *
 */
class GdcCrawler extends AbstractCrawler
{
    const NAME = 'Gdc';

    const SITE_URL = 'https://gensdeconfiance.fr';

    protected $username;
    protected $password;

    public function __construct()
    {
        parent::__construct();

        $this->name = self::NAME;
        $this->searchUrl = null;
    }

    public function getOffers($criterias, $excludedCrawlers = array())
    {
        parent::getOffers($criterias, $excludedCrawlers);

        try {
            $this->crawler = $this->client->request('GET', 'https://gensdeconfiance.fr/membre/connexion');
        } catch (RequestException $e) {
            echo(sprintf("EXCEPTION: %s\n", $e->getMessage()));

            return 0;
        }

        try {
            $button = $this->crawler->selectButton('Connexion');
            $form = $button->form();
            if ($button && $form) {
                $this->crawler = $this->client->submit(
                  $form,
                  array(
                    'loginMemberWithEmail[email]' => $this->username,
                    'loginMemberWithEmail[password]' => $this->password
                  )
                );
            }
        } catch (\InvalidArgumentException $e) {

        }


        $this->crawler = $this->client->request(
          'POST',
          'https://gensdeconfiance.fr/annonces',
          array(
            'adQuery[terms]' => 'paris',
            'adQuery[longitudeMax]' => '',
            'adQuery[longitudeMin]' => '',
            'adQuery[latitudeMax]' => '',
            'adQuery[latitudeMin]' => '',
            'adQuery[minPrice]' => '',
            'adQuery[minGuests]' => '',
            'adQuery[maxGuests]' => '',
            'adQuery[maxRooms]' => '',
            'adQuery[maxSquareMeters]' => '',
            'adQuery[minKilometers]' => '',
            'adQuery[maxKilometers]' => '',
            'adQuery[minYear]' => '',
            'adQuery[maxYear]' => '',
            'adQuery[clothingSize]' => '',
            'adQuery[departements]' => '',
            'adQuery[region]' => 'iledefrance',
            'adQuery[mapZoom]' => '5',
            'adQuery[category]' => 'realestate-rent',
            'adQuery[type]' => 'offering',
            'adQuery[networkIds]' => ',gdc,112,',
            'adQuery[orderColumn]' => 'displayDate',
            'adQuery[orderDirection]' => 'DESC',
            'adQuery[minRooms]' => $criterias[CrawlerService::MIN_NB_ROOM],
            'adQuery[maxPrice]' => '',
            'adQuery[minSquareMeters]' => $criterias[CrawlerService::MIN_SURFACE],
          )
        );
        $this->criterias = $criterias;

        $offers = $this->nodeFilter($this->crawler, '.ad-title a');
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

        $description = $this->nodeFilter($this->crawler, '#ad-description', $url);
        $description = $description ? $description->text() : '';

        $images = [];
        $imageNodes = $this->nodeFilter($this->crawler, '.ad-images img', $url);
        if($imageNodes) {
            $imageNodes->each(
                function (Crawler $node) use (&$images) {
                    $images[] = 'https:' . $node->attr('src');
                }
            );
        }

        $price = $this->nodeFilter($this->crawler, '.ad-price', $url);
        $price = $price ? $price->text() : '';

        $tel = $this->nodeFilter($this->crawler, '#contentInner #owner-phone span.btn-tip', $url);
        $tel = $tel ? $tel->text() : null;

        if($price != '' && intval(preg_replace('/\s/', '', $price)) > $this->criterias[CrawlerService::MAX_BUDGET]) {
            $isBlacklisted = 1;
        }

        return $this->offerManager->createOffer($title, $description, $images, $url, self::NAME, $price, null, null, $tel, $isBlacklisted);
    }

    public function isScheduled()
    {
        $authorizedHours = array(
          8,
          10,
          12,
          14,
          16,
          18,
          20,
          22,
          0
        );

        $currentHour = intval(date('H'));
        if (in_array($currentHour, $authorizedHours)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

}

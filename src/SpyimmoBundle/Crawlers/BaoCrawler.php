<?php

namespace SpyimmoBundle\Crawlers;

use Goutte\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use SpyimmoBundle\Services\CrawlerService;

/**
 * Class BaoCrawler
 *
 */
class BaoCrawler extends AbstractCrawler
{

    const NAME = 'Bao';

    const SEARCH_URL = 'https://www.bao.fr/annonce/catalogsearch/result/?q=&cat=15&contact_region=1175&price[to]=%s&surface[from]=%s';

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
        $this->searchUrl = sprintf(self::SEARCH_URL, CrawlerService::MAX_BUDGET, CrawlerService::MIN_SURFACE);

        $this->client = new Client(['timeout' => self::TIMEOUT]);

        try {
            $this->crawler = $this->client->request('GET', 'https://www.bao.fr/customer/account/login');
        } catch (RequestException $e) {
            echo(sprintf("EXCEPTION: %s\n", $e->getMessage()));

            return array(0, 0);
        }

        $form = $this->crawler->selectButton('Accéder à mon espace')->form();
        $this->crawler = $this->client->submit(
          $form,
          array(
            'login[username]' => $this->username,
            'login[password]' => $this->password
          )
        );

        $this->searchUrl = $this->generateUrl($this->searchUrl, $criterias);
        $crawler = $this->client->request('GET', $this->searchUrl);

        $offers = $this->nodeFilter($crawler, 'h2.title a');
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

        $description = $this->nodeFilter($this->crawler, '.annonce-description', $url);
        $description = $description ? $description->text() : '';

        $images = [];
        $imageNodes = $this->nodeFilter($this->crawler, '.annonce-images img', $url);
        if($imageNodes) {
            $imageNodes->each(
                function (Crawler $node) use (&$images) {
                    $images[] = $node->attr('src');
                }
            );
        }

        $price = $this->nodeFilter($this->crawler, '.annonce-view .price', $url);
        $price = $price ? $price->text() : '';

        return $this->offerManager->createOffer($title, $description, $images, $url, self::NAME, $price);

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

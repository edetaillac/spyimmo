<?php

namespace SpyimmoBundle\Services;

use Symfony\Component\Console\Style\SymfonyStyle;
use SpyimmoBundle\Crawlers\AbstractCrawler;
use SpyimmoBundle\Crawlers\BaoCrawler;
use SpyimmoBundle\Crawlers\GdcCrawler;
use SpyimmoBundle\Crawlers\OmmiCrawler;

class CrawlerService
{
    /**
     * @var AbstractCrawler[]
     */
    protected $crawlers;

    const MIN_NB_BEDROOM = 'nb_bedroom';
    const MIN_NB_ROOM = 'min_nb_room';
    const MAX_NB_ROOM = 'max_nb_room';
    const MIN_SURFACE = 'min_surface';
    const MAX_SURFACE = 'max_surface';
    const MIN_BUDGET = 'min_budget';
    const MAX_BUDGET = 'max_budget';

    public function __construct()
    {
        $this->crawlers = array();
    }

    /**
     * @return AbstractCrawler[]
     */
    public function getCrawlers()
    {
        return $this->crawlers;
    }

    /**
     * @param mixed AbstractCrawler
     */
    public function addCrawler($crawler)
    {
        $this->crawlers[] = $crawler;
    }

    public function crawl($forceCrawler = null)
    {
        $cptNewOffers = 0;

        foreach ($this->crawlers as $crawler) {

            if ($forceCrawler && !$crawler->isForced($forceCrawler) ) {
                continue;
            }

            $cptNewOffers += $crawler->getOffers(
              array(
                self::MIN_NB_BEDROOM => 1,
                self::MIN_NB_ROOM    => 2,
                self::MIN_SURFACE    => 25,
                self::MAX_BUDGET     => 950
              ),
              array()
            );

        }

        return $cptNewOffers;
    }
}

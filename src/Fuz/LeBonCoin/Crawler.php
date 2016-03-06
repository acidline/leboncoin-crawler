<?php

namespace Fuz\LeBonCoin;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Crawler
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get the list of all available ads
     *
     * Returns an array of ads where one ad has the following format:
     *
     * array(7) {
     *     ["url"]=>
     *     string(52) "https://www.leboncoin.fr/velos/934826585.htm?ca=12_s"
     *     ["image"]=>
     *     string(81) "https://img1.leboncoin.fr/thumbs/010/010a091e6f370cf14f1bf446be768a92e6342d7f.jpg"
     *     ["title"]=>
     *     string(50) "Collier BBB 31.8mm ALU dérailleur avant à BRASER"
     *     ["category"]=>
     *     string(6) "Vélos"
     *     ["location"]=>
     *     string(33) "Carrières-sous-Poissy / Yvelines"
     *     ["price"]=>
     *     string(7) "10 €"
     *     ["date"]=>
     *     string(18) "Aujourd'hui, 14:12"
     * }
     *
     * @param srring    $url        the url that leads to search results
     * @param int       $maxAds     max number of ads to fetch (0 = gets all ads)
     *
     * @return array    $ads
     */
    public function getAds($url, $maxAds = 0)
    {
        $ads        = [];
        $pageNumber = 1;
        $fetchedAds = 0;

        do {
            $crawler = $this->client->request('GET', $url, [
                'o' => $pageNumber,
            ]);

            $totalAds = str_replace(' ', '',
               $crawler->filter('#listingAds section header nav a')->first()->filter('span')->text()
            );

            $nbAdsInPage = $crawler->filter('#listingAds > section > ul > li')->count();

            $ads += $this->_fetchAds($crawler, $fetchedAds, $maxAds);

            $fetchedAds += $nbAdsInPage;
            $pageNumber++;
        } while ($fetchedAds < $totalAds && ($maxAds == 0 || $fetchedAds < $maxAds));

        return array_slice($ads, 0, $maxAds ? $maxAds : count($ads));
    }

    protected function _fetchAds(DomCrawler $crawler)
    {
        $ads = [];

        $crawler->filter('#listingAds > section > ul > li')->each(function(DomCrawler $node) use (&$ads) {
            $ad = [];

            // Ad URL
            $ad['url'] = 'https:' . $node->filter('a')->attr('href');

            // Ad image
            $ad['image'] = 'http://static.leboncoin.fr/img/no-picture.png';
            $image       = $node->filter('.item_image .item_imagePic span');
            if ($image->count()) {
                $ad['image'] = 'https:' . $image->attr('data-imgsrc');
            }

            // Ad title
            $ad['title'] = 'No given title';
            $title       = $node->filter('.item_infos .item_title');
            if ($title->count()) {
                $ad['title'] = trim($title->text());
            }

            // Ad category
            $ad['category'] = 'No given category';
            $category       = $node->filter('.item_infos p.item_supp');
            if ($category->count()) {
                $ad['category'] = trim($category->eq(0)->text());
            }

            // Ad location
            $ad['location'] = 'No given location';
            $location       = $node->filter('.item_infos p.item_supp');
            if ($location->count() >= 2) {
                $ad['location'] = trim(preg_replace("|\s+|", ' ', $location->eq(1)->text()));
            }

            // Ad price
            $ad['price'] = 'No given price';
            $price       = $node->filter('.item_infos h3.item_price');
            if ($price->count()) {
                $ad['price'] = trim($price->text());
            }

            // Ad date
            $ad['date'] = 'Unknown';
            $date       = $node->filter('.item_infos aside p');
            if ($date->count()) {
                $ad['date'] = trim($date->text());
            }

            $ads[] = $ad;
        });

        return $ads;
    }
}
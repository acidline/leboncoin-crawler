# LeBonCoin Crawler

A simple class that crawls LeBonCoin ads according to a search URL.

## Installation

Here are instructions to get started with the library.

```sh
composer require ninsuo/leboncoin-crawler
```

## Run

```php
    $crawler = new Fuz\LeBonCoin\Crawler();
    $ads = $crawler->getAds('http://www.leboncoin.fr/annonces/offres/ile_de_france/', 100);
```

See also: https://github.com/ninsuo/leboncoin-alert
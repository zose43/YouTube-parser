<?php

namespace App\Console\Parsers;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

abstract class Parser
{
    abstract protected function filterData( string $html ): array;

    abstract public function getData( string $word ): array;

    protected function getClient(): Client
    {
        return new Client();
    }

    protected function getCrawler(): Crawler
    {
        return new Crawler();
    }
}
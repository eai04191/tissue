<?php

namespace App\MetadataResolver;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class PornHubResolver implements Resolver
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var OGPResolver
     */
    private $ogpResolver;

    public function __construct(Client $client, OGPResolver $ogpResolver)
    {
        $this->client = $client;
        $this->ogpResolver = $ogpResolver;
    }

    public function resolve(string $url): Metadata
    {
        // if (preg_match('~www\.xtube\.com/video-watch/.*-(\d+)$~', $url, $matches) !== 1) {
        //     throw new \RuntimeException("Unmatched URL Pattern: $url");
        // }
        // $videoid = $matches[1];

        $res = $this->client->get($url);
        if ($res->getStatusCode() === 200) {
            $html = (string) $res->getBody();
            $metadata =  $this->ogpResolver->parse($html);
            $crawler = new Crawler($html);

            $js = $crawler->filter('#player script')->text();
            if (preg_match('~({.+});~', $js, $matches)) {
                $json = $matches[1];
                $data = json_decode($json, true);

                $metadata->title = $data['video_title'];
                $metadata->image = $data['image_url'];
            }

            $metadata->tags = $crawler->filter('.video-detailed-info a:not(.add-btn-small)')->extract('_text');


            return $metadata;
        } else {
            throw new \RuntimeException("{$res->getStatusCode()}: $url");
        }
    }
}

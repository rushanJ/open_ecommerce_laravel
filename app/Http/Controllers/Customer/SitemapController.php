<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(SeoService $seo): Response
    {
        $items = $seo->sitemapItems();

        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach ($items as $item) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.htmlspecialchars($item['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>';
            if (! empty($item['lastmod'])) {
                $lines[] = '    <lastmod>'.$item['lastmod']->clone()->utc()->format('Y-m-d\TH:i:s\Z').'</lastmod>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        $xml = implode("\n", $lines);

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}

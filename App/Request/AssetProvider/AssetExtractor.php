<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Request\Assets;

class AssetExtractor
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\HTTP\Client $http,
        \FishPig\WordPress\App\Request\Assets\ScriptPackager $scriptPackager
    ) {
        $this->http = $http;
        $this->scriptPackager = $scriptPackager;
    }

    /**
     * @param  string $url
     * @return string
     */
    public function extractFromUrl(string $url): string
    {
        return $this->extractFromString(
            $this->http->get(
                $url
            )
        );
    }

    /**
     * @param  string $html
     * @return string
     */
    public function extractFromString(string $html): string
    {
        $assets = $this->getAssetsFromHtml($html);

        if ($assets['script']) {
            $assets['script'] = $this->scriptPackager->package($scripts);
        }

        print_r($assets);
        exit;
    }

    /**
     *
     */
    public function addBodyClassNames($html): string
    {
        $classString = '';
        
        
        if (($originalBodyPos = preg_match('/<body[^>]+class="(.*)"/Uis', $html, $classMatches))) {
            $classString .= $classMatches[1] . ' ' ;
        }
        
        if (preg_match('/<body[^>]+class="(.*)"/Uis', $this->getWPHtmlForCurrentUrl(), $classMatches)) {
            $classString .= $classMatches[1] . ' ' ;
        }

        $classString = implode(' ', array_filter(array_unique(explode(' ', trim($classString)))));
        
        if (!$classString) {
            return $html;
        }
        
        if ($originalBodyPos) {
            $html = preg_replace('/(<body[^>]+class=").*"/iUs', '$1' . $classString . '"', $html);
        }
        
        return $html;
    }

    /**
     * @param  string $html
     * @return array
     */
    private function getAssetsFromHtml(string $html): array
    {
        $assets = [];
        
        foreach ([
            'script' => '/<script[^>]{0,}>.*<\/script>/Us', 
            'style' => '/<style[^>]{0,}>.*<\/style>/Us',
            'link' => '/<link[^>]*stylesheet[^>]*>/Us'
        ] as $type => $regex) {
            $assets[$type] = [];

            if (preg_match_all(
                $regex,
                $html, 
                $tagMatches, 
                PREG_OFFSET_CAPTURE)
            ) {
                foreach ($tagMatches[0] as $tagMatch) {
                    $assets[$type][$tagMatch[1]] = $tagMatch[0];
                }
                
                ksort($assets[$type], SORT_NUMERIC);
            }
        }

        return $assets;
    }
}

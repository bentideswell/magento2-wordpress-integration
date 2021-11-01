<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

class Client
{
    /**
     * @const int
     */
    const CACHE_VERSION = 1;

    /**
     * @param \FishPig\WordPress\App\Cache $cache
     */
    public function __construct(
        \FishPig\WordPress\App\Cache $cache,
        \FishPig\WordPress\App\Url $url
    ) {
        $this->cache = $cache;
        $this->url = $url;
    }

    /**
     * @param  string $url
     * @return string
     */
    public function get($url): string
    {   
        $cacheKey = $this->getCacheKey($url);
        
        if ($data = $this->cache->load($cacheKey)) {
            return $data;
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $data = curl_exec($ch);

        curl_close($ch);

        if ($cacheKey) {
            $this->cache->save($cacheKey, $data);
        }

        return $data;
    }
    
    /**
     * @param  string $url
     * @return string
     */
    private function getCacheKey($url): string
    {
        return md5(get_class($this) . self::CACHE_VERSION . $url);
    }
}

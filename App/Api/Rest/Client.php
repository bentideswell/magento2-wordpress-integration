<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api\Rest;

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
     * @param  string $endpoint
     * @return string
     */
    public function get($endpoint): string
    {
        // Converts $url into a full rest URL
        $url = $this->url->getRestUrl($endpoint);
        
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
     * @param  string $endpoint
     * @return []|false
     */
    public function getJson($endpoint)
    {
        if ($data = $this->get($endpoint)) {
            $firstChar = substr($data, 0, 1);
           
            if (!in_array($firstChar, ['{', '['])) {
                throw new \Exception('Invalid JSON response.');
            }
            
            return json_decode($data, true);
        }
       
        return false;
    }
    
    /**
     * @param  string $url
     * @return string
     */
    private function getCacheKey($url): string
    {
        return md5(self::CACHE_VERSION . $url);
    }
}

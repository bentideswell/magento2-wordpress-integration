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
    const CACHE_VERSION = 2;

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
            $this->log(date('Y-m-d H:i:s') . ' - CACHED - ' . $url);
            return $data;
        }

        $this->log(date('Y-m-d H:i:s') . ' -  GET - ' . $url);
        
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $data = curl_exec($ch);

        $httpCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        
        curl_close($ch);

        if (strpos($data, '<h1>404</h1>') !== false) {
            $httpCode = 404;
        }

        if ($httpCode !== 200) {
            throw new \FishPig\WordPress\App\Http\InvalidStatusException($url, $httpCode);
        }

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
    
    /**
     * @param string $msg
     * @return void
     */
    private function log(string $msg): void
    {
        $e = new \Exception('');
        $msg .= "\n\n" . preg_replace('/\#6.*$/s', '', $e->getTraceAsString()) . PHP_EOL;
        
        
        file_put_contents(BP . '/var/log/wordpress-api-requests.log', $msg . PHP_EOL, FILE_APPEND);   
    }
}

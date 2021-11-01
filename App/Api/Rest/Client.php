<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api\Rest;

class Client extends \FishPig\WordPress\App\HTTP\Client
{
    /**
     * @param  string $url
     * @return string
     */
    public function get($url): string
    {
        // Converts $url into a full rest URL
        $url = $this->url->getRestUrl($url);

        return parent::get($url);
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
}

<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Api
{
    /**
     * @var array
     */
    private $data = null;

    /**
     * @param \FishPig\WordPress\App\Api\Rest\Client $apiClient
     */
    public function __construct(
        \FishPig\WordPress\App\Api\Rest\Client $apiClient
    ) {
        $this->apiClient = $apiClient;
    }
    
    /**
     * @param $key = null
     * @return mixed
     */
    public function getData($key = null)
    {
        if ($this->data === null) {
            $this->data = $this->apiClient->getJson('/fishpig/v1/data');
        }
        
        if ($key === null) {
            return $this->data;
        }
        
        if (!isset($this->data[$key])) {
            throw new \Exception('Unable to get ' . $key . ' from API data.');
        }
        
        return $this->data[$key];
    }
}

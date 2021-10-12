<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Mode\External\Theme;

class RemoteHashRetriever
{
    /**
     * @var string
     */
    private $hash = null;
    
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Api\Rest\Client $apiClient
    ) {
        $this->apiClient = $apiClient;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        if ($this->hash === null) {
            $this->hash = '';
            
            if ($data = $this->apiClient->getJson('/fishpig/v1/theme-hash')) {
                if (isset($data['hash'])) {
                    $this->hash = $data['hash'];
                }
            }
        }

        return $this->hash;
    }
}

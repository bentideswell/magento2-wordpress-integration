<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Mode\External;

class Theme implements \FishPig\WordPress\Api\Data\App\Integration\ThemeInterface
{
    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Theme\LocalHashGenerator $localHashGenerator,
        \FishPig\WordPress\App\Integration\Mode\External\Theme\RemoteHashRetriever $remoteHashRetriever,
        \FishPig\WordPress\App\Api\Rest\Client $apiClient
    ) {
        $this->localHashGenerator = $localHashGenerator;
        $this->remoteHashRetriever = $remoteHashRetriever;
        $this->apiClient = $apiClient;
    }
    
    /**
     * @return string
     */
    public function getLocalHash(): string
    {
        return $this->localHashGenerator->getHash();
    }
    
    /**
     * @return string
     */
    public function getRemoteHash(): string
    {
        return $this->remoteHashRetriever->getHash();
    }
}

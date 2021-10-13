<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Theme
{
    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\LocalHashGenerator $localHashGenerator,
        \FishPig\WordPress\App\Theme\RemoteHashRetrieverResolver $remoteHashRetrieverResolver
    ) {
        $this->localHashGenerator = $localHashGenerator;
        $this->remoteHashRetrieverResolver = $remoteHashRetrieverResolver;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->getRemoteHash() !== '';
    }

    /**
     * @return bool
     */
    public function isLatestVersion(): bool
    {
        return $this->isInstalled() && $this->getLocalHash() === $this->getRemoteHash();
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
        return $this->remoteHashRetrieverResolver->resolve()->getHash();
    }
}

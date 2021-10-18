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
     * @var array
     */
    private $themeMods = null;

    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\LocalHashGenerator $localHashGenerator,
        \FishPig\WordPress\App\Theme\RemoteHashRetrieverResolver $remoteHashRetrieverResolver,
        \FishPig\WordPress\App\Option $option
    ) {
        $this->localHashGenerator = $localHashGenerator;
        $this->remoteHashRetrieverResolver = $remoteHashRetrieverResolver;
        $this->option = $option;
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
    
    /**
     * @return mixed
     */
    public function getThemeMods($key = null)
    {
        if ($this->themeMods === null) {
            $this->themeMods = $this->option->getUnserialized('theme_mods_fishpig');
        }

        if ($this->themeMods) {
            if ($key !== null) {
                return isset($this->themeMods[$key]) ? $this->themeMods[$key] : false;
            }

            return $this->themeMods;
        }

        return false;
    }
}

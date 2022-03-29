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
     *
     */
    const THEME_NAME = 'fishpig';

    /**
     * @var array
     */
    private $themeMods = null;

    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\LocalHashProvider $localHashProvider,
        \FishPig\WordPress\App\Theme\RemoteHashProvider $remoteHashProvider,
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \FishPig\WordPress\App\Cache $cache
    ) {
        $this->localHashProvider = $localHashProvider;
        $this->remoteHashProvider = $remoteHashProvider;
        $this->optionRepository = $optionRepository;
        $this->cache = $cache;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return self::THEME_NAME === $this->getEnabledThemeName() && $this->getRemoteHash() !== '';
    }

    /**
     * @return bool
     */
    public function isLatestVersion(): bool
    {
        return $this->isInstalled() && $this->getLocalHash() === $this->getRemoteHash();
    }
    
    /**
     * @return ?string
     */
    public function getEnabledThemeName(): ?string
    {
        return $this->optionRepository->get('template');
    }
    /**
     * Local theme hash (file collection + hashing) is cached for an hour
     * Flushing or disabling the cache will force a rebuild of hash
     * Access via CLI will also force a rebuild of local hash
     *
     * @return string
     */
    public function getLocalHash(): string
    {
        $cacheKey = 'theme_local_hash';
        
        if (PHP_SAPI !== 'cli' && ($localHash = $this->cache->load($cacheKey))) {
            return $localHash;
        }

        $localHash = $this->localHashProvider->getHash();

        $this->cache->save($localHash, $cacheKey, [], 604800);

        return $localHash;
    }
    
    /**
     * @return string
     */
    public function getRemoteHash(): string
    {
        return $this->remoteHashProvider->getHash();
    }
    
    /**
     * @return mixed
     */
    public function getThemeMods($key = null)
    {
        if ($this->themeMods === null) {
            $this->themeMods = $this->optionRepository->getUnserialized('theme_mods_' . self::THEME_NAME);
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

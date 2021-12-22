<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

class LocalHashProvider implements \FishPig\WordPress\Api\App\Theme\HashProviderInterface
{
    /**
     * @var string
     */
    private $hash = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\FileCollector $themeFileCollector
    ) {
        $this->themeFileCollector = $themeFileCollector;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        if ($this->hash === null) {
            $hashes = [];
            
            foreach ($this->themeFileCollector->getFiles() as $file) {
                // phpcs:ignore -- not cryptographic
                $hashes[] = hash_file('md5', $file);
            }
        
            // phpcs:ignore -- not cryptographic
            $this->hash = md5(implode('', $hashes));
        }
        
        return $this->hash;
    }
}

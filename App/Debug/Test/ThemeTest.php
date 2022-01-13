<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class ThemeTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\PackageBuilder $packageBuilder,
        \FishPig\WordPress\App\Theme\PackagePublisher $packagePublisher
        
    ) {
        $this->packageBuilder = $packageBuilder;
        $this->packagePublisher = $packagePublisher;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $filename = $this->packageBuilder->getFilename();
        
        unlink($filename); // Delete file as it may be old
        
        // Publish, which triggers recreation of theme file
        $this->packagePublisher->publish();
        
        if (!is_file($this->packageBuilder->getFilename())) {
            throw new \Exception('Unable to create theme file at ' . $this->packageBuilder->getFilename());
        }
    }
}

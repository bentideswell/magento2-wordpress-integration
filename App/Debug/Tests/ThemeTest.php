<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Tests;

class ThemeTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     * @auto
     */
    protected $themeTest = null;

    /**
     * @auto
     */
    protected $themeBuilder = null;

    /**
     * @auto
     */
    protected $themeDeployer = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Tests\ThemeTest $themeTest,
        \FishPig\WordPress\App\Theme\Builder $themeBuilder,
        \FishPig\WordPress\App\Theme\Deployer $themeDeployer
    ) {
        $this->themeTest = $themeTest;
        $this->themeBuilder = $themeBuilder;
        $this->themeDeployer = $themeDeployer;
    }

    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        // Throws on error
        $this->themeBuilder->getBlob();

        // Get theme blob as local file
        $localFile = $this->themeBuilder->getLocalFile();

        if (!is_file($localFile)) {
            throw new \Exception(
                sprintf(
                    'Cannot create local theme file at "%s"',
                    $localFile
                )
            );
        }

        unlink($localFile); // Delete file as it's good to be clean

        $this->themeDeployer->deployUsing('local');
        $this->themeDeployer->deployUsing('http');
        $this->themeDeployer->deploy(true);

        // Check theme version
        $this->themeTest->runTest();
    }
}

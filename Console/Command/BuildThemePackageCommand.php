<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class BuildThemePackageCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @const string
     */
    const INSTALL_PATH = 'install-path';

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\PackageBuilder $packageBuilder,
        \FishPig\WordPress\App\Theme\PackageDeployer $packageDeployer,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        string $name = null
    ) {
        $this->packageBuilder = $packageBuilder;
        $this->packageDeployer = $packageDeployer;
        $this->fileDriver = $fileDriver;
        parent::__construct($name);
    }

    /**
     * @return $this
     */
    protected function configure()
    {
        $this->setName('fishpig:wordpress:build-theme');
        $this->setDescription('Generate a ZIP file containing the FishPig WordPress theme.');
        $this->setDefinition([
            new InputOption(self::INSTALL_PATH, null, InputOption::VALUE_OPTIONAL, 'Optional local installation path')
        ]);
        
        return parent::configure();
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $packageFile = $this->packageBuilder->getFilename();

            if ($installPath = $input->getOption(self::INSTALL_PATH)) {
                $installPath = $this->fileDriver->getRealPath($installPath);

                if (!$installPath) {
                    throw new \FishPig\WordPress\App\Exception(
                        'Invalid install path. Package file is at ' . $packageFile
                    );
                }

                $this->packageDeployer->deploy($packageFile, $installPath);

                $output->writeLn(
                    "Theme installed to WordPress at $installPath. Visit a WP Admin page to complete the installation."
                );
            } else {
                $output->writeLn($packageFile);
            }
        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
        }
    }
}

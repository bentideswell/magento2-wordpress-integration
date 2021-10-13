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
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\PackageBuilder $packageBuilder,
        string $name = null
    ) {
        $this->packageBuilder = $packageBuilder;

        parent::__construct($name);
    }

    /**
     * @return $this
     */
    protected function configure()
    {
        $this->setName('fishpig:wordpress:build-theme');
        $this->setDescription('Generate a ZIP file containing the FishPig WordPress theme.');
        $this->setDefinition([]);
        
        return parent::configure();
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeLn($this->packageBuilder->getFilename());
        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
        }
    }
}

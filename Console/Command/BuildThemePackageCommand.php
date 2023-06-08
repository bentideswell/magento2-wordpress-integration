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
use FishPig\WordPress\App\Exception;
use FishPig\WordPress\App\Theme;
use FishPig\WordPress\App\Theme\DeploymentInterface;
use FishPig\WordPress\App\Theme\DeploymentException;

class BuildThemePackageCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     *
     */
    const DEPLOY = 'deploy';
    const FORCE = 'force';
    const ZIP = 'zip';
    const LOCAL = 'local';
    const HTTP = 'http';

    /**
     *
     */
    private $theme = null;

    /**
     *
     */
    private $themeBuilder = null;

    /**
     *
     */
    private $themeDeployer = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme $theme,
        \FishPig\WordPress\App\Theme\Builder $themeBuilder,
        \FishPig\WordPress\App\Theme\Deployer $themeDeployer,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        string $name = null
    ) {
        $this->theme = $theme;
        $this->themeBuilder = $themeBuilder;
        $this->themeDeployer = $themeDeployer;
        parent::__construct($name);
    }

    /**
     * @return $this
     */
    protected function configure()
    {
        $this->setName('fishpig:wordpress:theme');
        $this->setDescription('Deploy or generate the FishPig theme.');
        $this->setDefinition([
            new InputOption(
                self::DEPLOY,
                null,
                InputOption::VALUE_NONE,
                'Auto deploy theme to WordPress via the DB'
            ),
            new InputOption(
                self::FORCE,
                null,
                InputOption::VALUE_NONE,
                'Deploys theme even if version matches.'
            ),
            new InputOption(
                self::ZIP,
                null,
                InputOption::VALUE_NONE,
                'Generates a ZIP locally, rather than auto deploying.'
            ),
            new InputOption(
                self::LOCAL,
                null,
                InputOption::VALUE_NONE,
                'Force using the local deployment service'
            ),
            new InputOption(
                self::HTTP,
                null,
                InputOption::VALUE_NONE,
                'Force using the HTTP deployment service'
            ),
        ]);

        return parent::configure();
    }

    /**
     *
     */
    public function getAliases()
    {
        return [
            'fishpig:wordpress:build-theme'
        ];
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::ZIP)) {
            $output->write(
                $this->themeBuilder->getLocalFile()
            );
            return 0;
        }

        if (!$input->getOption(self::DEPLOY)) {
            throw new Exception('No input arguments provided.');
        }

        if ($this->themeDeployer->isLatestVersion() && !$input->getOption(self::FORCE)) {
            $output->writeLn(
                sprintf(
                    '<comment>Skipped</comment> - theme "%s" version "%s" already deployed. Use --force to reinstall',
                    Theme::THEME_NAME,
                    $this->theme->getRemoteHash()
                )
            );
            // Success
            return 0;
        }

        if ($input->getOption(self::LOCAL)) {
            $deploymentId = $this->themeDeployer->deployUsing('local');
        } elseif ($input->getOption(self::HTTP)) {
            $deploymentId = $this->themeDeployer->deployUsing('http');
        } else {
            $deploymentId = $this->themeDeployer->deploy();
        }

        if ($deploymentId === null) {
            $output->writeLn(
                sprintf(
                    '<error>Fail</error> - theme "%s" could not be updated. Check logs.',
                    Theme::THEME_NAME
                )
            );

            return 1; // Error
        }

        $output->writeLn(
            sprintf(
                '<info>Success</info> - theme "%s" version "%s" installed using "%s" deployment.',
                Theme::THEME_NAME,
                $this->theme->getRemoteHash(),
                $deploymentId
            )
        );
        return 0; // Success
    }
}

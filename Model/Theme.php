<?php
/**
 *
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\OptionManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use FishPig\WordPress\Model\DirectoryList;
use FishPig\WordPress\Model\Logger;
use Magento\Framework\Module\Dir as ModuleReader;
use FishPig\WordPress\Model\Integration\IntegrationException;
use Exception;

class Theme
{
    /**
     * @var
     */
    const THEME_NAME = 'fishpig';

    /**
     * @var
     */
    protected $optionManager;

    /**
     * @var
     */
    protected $scopeConfig;

    /**
     * @var
     */
    protected $storeManager;

    /**
     * @var
     */
    protected $wpDirectoryList;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ModuleReader
     */
    protected $moduleReader;

    /**
     * @var array
     */
    protected $themeSourceModules;

    /**
     * @var array
     */
    protected $themeMods;

    /**
     *
     */
    public function __construct(
        OptionManager $optionManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        State $state,
        DirectoryList $wpDirectoryList,
        Logger $logger,
        ModuleReader $moduleReader,
        array $themeSourceModules = []
    )
    {
        $this->optionManager = $optionManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->wpDirectoryList = $wpDirectoryList;
        $this->logger = $logger;
        $this->moduleReader = $moduleReader;
        $this->themeSourceModules = $themeSourceModules;
    }

    /**
     * @return
     */
    public function validate()
    {        
        try {
            if (!$this->wpDirectoryList->isValidBasePath()) {
                IntegrationException::throwException('Empty or invalid WordPress path.');
            }

            if (!is_dir($this->wpDirectoryList->getContentDir())) {
                IntegrationException::throwException('Unable to find the wp-content directory at ' . $this->wpDirectoryList->getContentDir());
            }
            
            if (!is_dir($this->wpDirectoryList->getPluginDir())) {
                IntegrationException::throwException('Unable to find the plugins directory at ' . $this->wpDirectoryList->getPluginDir());
            }

            if (!is_dir($this->wpDirectoryList->getThemeDir())) {
                IntegrationException::throwException('Unable to find the themes directory at ' . $this->wpDirectoryList->getThemeDir());
            }
            
            // Now let's find some source directories
            $sourceDirs = [];

            foreach($this->themeSourceModules as $sourceModule => $sourcePrimaryFile) {
                $moduleDir = $this->moduleReader->getDir($sourceModule);

                if ($moduleDir && is_dir($moduleDir . '/wptheme')) {
                    $sourceDirs[$moduleDir . '/wptheme'] = $sourcePrimaryFile;
                }
            }

            if (!$sourceDirs) {
                IntegrationException::throwException('Unable to find any WordPress theme source directories.');
            }

            $targetDir = $this->getTargetDir();

            // Either theme not installed or version changes
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);

                if (!is_dir($targetDir)) {
                    IntegrationException::throwException(
                        'The FishPig WordPress theme is not installed and due to the permissions of the WordPress theme folder, it cannot be installed automatically. Please copy the contents of app/code/FishPig/WordPress/wptheme to the wp-content/themes/fishpig folder.'
                    );
                }
            }

            foreach($sourceDirs as $sourceDir => $sourcePrimaryFilename) {
                $sourcePrimaryFile = $sourceDir . '/' . $sourcePrimaryFilename;
                $targetPrimaryFile = $targetDir . '/' . $sourcePrimaryFilename;

                if (!is_file($targetPrimaryFile) || md5_file($sourcePrimaryFile) !== md5_file($targetPrimaryFile)) {
                    // Get source files. Loop through and copy to WordPress
                    $sourceFiles = scandir($sourceDir);

                    foreach($sourceFiles as $sourceFilename) {
                        if (trim($sourceFilename, '.') === '') {
                            continue;
                        }

                        if ($sourceFilename === 'local.php') {
                            continue;
                        }

                        $sourceFile = $sourceDir . '/' . $sourceFilename;
                        $targetFile = $targetDir . '/' . $sourceFilename;

                        // Don't allow symlinks (below may cover this, but just to be sure)
                        if (is_link($sourceFile)) {
                            continue;
                        }

                        // Ignore directories.
                        if (!is_file($sourceFile)) {
                            continue;
                        }

                        $sourceData = file_get_contents($sourceFile);
                        $targetData = file_exists($targetFile) ? file_get_contents($targetFile) : '';

                        if ($sourceData !== $targetData) {
                          if (!$this->isFileWriteable($targetFile)) {
                              IntegrationException::throwException('Unable to install/upgrade the FishPig WordPress theme due to permissions. The file that triggered the error is ' . $targetFile);
                          }

                            file_put_contents($targetFile, $sourceData);
                        }
                    }
                }
            }

            if (!$this->isActive()) {
                IntegrationException::throwException(
                    'The FishPig WordPress theme is installed but is not active. Please login to the WordPress Admin and enable it.'
                );
            }
        }
        catch (Exception $e) {
            if ($this->state->getAreaCode() === 'adminhtml') {
                throw $e;
            }

            $this->logger->error($e);
            
            throw $e;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isFileWriteable($file)
    {
        return is_file($file) && is_writeable($file) || !is_file($file) && is_writable(dirname($file));
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->optionManager->getOption('template') === self::THEME_NAME && $this->optionManager->getOption('stylesheet') === self::THEME_NAME;
    }

    /**
     * @return string
     */
    public function getTargetDir()
    {
        return $this->wpDirectoryList->getThemeDir() . '/' . self::THEME_NAME;
    }

    /**
     * @return string
     */
    public function getSourceDir()
    {
        return $this->getModuleDir() . '/wptheme';
    }

    /**
     * @return bool
     */
    public function isThemeIntegrated()
    {
        return (int)$this->scopeConfig->getValue(
            'wordpress/setup/theme_integration', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 
            (int)$this->storeManager->getStore()->getId()
        ) === 1;
    }

    /**
     * @return string
     */
    protected function getModuleDir()
    {
        return dirname(__DIR__);
    }

    /**
     * @return mixed
     */
    public function getThemeMods($key = null)
    {
        if (!$this->isActive()) {
            return false;
        }

        if (!isset($this->themeMods)) {
            $this->themeMods = [];

            if ($themeMods = $this->optionManager->getOption('theme_mods_' . self::THEME_NAME)) {
                $this->themeMods = @unserialize($themeMods);
            }
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

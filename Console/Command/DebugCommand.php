<?php
/**
 *
 */
namespace FishPig\WordPress\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class DebugCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @const string
     */
    const FORMAT  = 'format';

    /**
     * @const string
     */
    const FORMAT_JSON = 'json';
    const FORMAT_ARRAY = 'array';
    
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Module\Dir $moduleDir,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \FishPig\WordPress\App\Logger $logger,
        string $name = null
    ) {
        $this->fullModuleList = $fullModuleList;
        $this->moduleManager = $moduleManager;
        $this->moduleDir = $moduleDir;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * @return $this
     */
    protected function configure()
    {
        $this->setName('fishpig:wordpress:debug');
        $this->setDescription('Generate a debugging string for WordPress Integration.');

        $options = [
            new InputOption(self::FORMAT, null, InputOption::VALUE_OPTIONAL, 'Output format. Defaults to json'),
        ];

        $this->setDefinition($options);
        
        return parent::configure();
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* Get the output format */
        $format = $input->getOption(self::FORMAT) ? $input->getOption(self::FORMAT) : self::FORMAT_JSON;
        
        if (!in_array($format, $this->getAllowedOutputFormats())) {
            $format = self::FORMAT_JSON;
        }

        $debug = [
            'magento' => $this->getMagentoDebugData(),
            'modules' => $this->getModulesDebugData(),
            'config' => $this->getConfigDebugData(),
        ];
        
        if ($format === self::FORMAT_ARRAY) {
            $output->writeLn(
                print_r($debug, true) // phpcs:ignore
            );
        } else {
            $output->writeLn(json_encode($debug, JSON_UNESCAPED_SLASHES));
        }
    }
     
    /**
     * @return array|false
     */
    private function getMagentoDebugData()
    {
        $data = [
            'version' => $this->productMetadata->getVersion(),
            'edition' => $this->productMetadata->getEdition(),
            'stores' => count($this->storeManager->getStores(false)),
        ];
        
        return $data;
    }
    
    /**
     * @return array|false
     */
    private function getModulesDebugData()
    {
        $modules = [];
        
        foreach ($this->fullModuleList->getAll() as $module) {
            $moduleName = $module['name'];
            
            if (stripos($moduleName, 'FishPig') === false) {
                continue;
            }
            
            $moduleDir = $this->moduleDir->getDir($moduleName, '');
            $modules[$moduleName] = [
                'is_enabled' => (int)$this->moduleManager->isEnabled($moduleName),
                'path' => str_replace(BP . '/', '', $moduleDir),
                'version' => $this->getComposerJsonVersion($moduleDir),
                'license' => $this->getKey($moduleName, $moduleDir)
            ];
        }
        
        return $modules;
    }
    
    /**
     * @return false|array
     */
    private function getConfigDebugData()
    {
        $db = $this->resourceConnection->getConnection('');
        
        return $db->fetchAll(
            $db->select()
                ->from(
                    $this->resourceConnection->getTableName('core_config_data'),
                    [
                        'path',
                        'value',
                        'scope',
                        'scope_id',
                    ]
                )->where(
                    'path LIKE ?',
                    'wordpress%'
                )
        );
    }

    /**
     * @return string
     */
    private function getComposerJsonVersion(string $path)
    {
        $composerFile = $path . '/composer.json';
        
        // phpcs:ignore -- is_file
        if (!is_file($composerFile)) {
            return false;
        }

        // phpcs:ignore -- file_get_contents
        $jsonString = file_get_contents($composerFile);
        $json = json_decode($jsonString, true);
        
        if (!$json) {
            throw new \FishPig\WordPress\App\Exception('Unable to parse JSON.');
        }

        if (!empty($json['version'])) {
            return $json['version'];
        }
        
        return false;
    }

    /**
     * @return array
     */
    private function getAllowedOutputFormats(): array
    {
        return [
            self::FORMAT_JSON,
            self::FORMAT_ARRAY
        ];
    }
    
    /**
     *
     */
    private function getKey($module, $path)
    {
        try {
            if (strpos($module, 'WordPress_') !== false) {
                $className = str_replace('WordPress\\', 'WordPress_', str_replace('_', '\\', $module));
            } else {
                $className = str_replace('_', '\\', $module);
            }
            
            $className .= '\\Helper\\License';
            
            if (!class_exists($className)) {
                return false;
            }
            
            if ($object = \Magento\Framework\App\ObjectManager::getInstance()->get($className)) {
                if (is_callable($object, 'getLicenseCode')) {
                    return $object->getLicenseCode();
                }
                
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
            return false;
        }
        
        return false;
    }
}

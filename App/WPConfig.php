<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class WPConfig
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->appMode = $appMode;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
    }
    
    /**
     * @param  ?string $key = null
     * @return mixed
     */
    public function getData($key = null, $default = null)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->data[$storeId])) {
            $this->appMode->requireLocalMode();
            
            $this->data[$storeId] = [];

            if ($this->directoryList->isBasePathValid() === false) {
                throw new IntegrationFatalException(
                    (string)__('Unable to find a WordPress installation using the path provided.')
                );
            }

            $wpConfig = $this->directoryList->getBaseDirectory()->readFile('wp-config.php');

            // Cleanup comments
            $wpConfig = str_replace("\n", "\n\n", $wpConfig);
            $wpConfig = preg_replace('/\n\#[^\n]{1,}\n/', "\n", $wpConfig);
            $wpConfig = preg_replace('/\n\\/\/[^\n]{1,}\n/', "\n", $wpConfig);
            $wpConfig = preg_replace('/\n\/\*.*\*\//Us', "\n", $wpConfig);
    
            if (!preg_match_all(
                '/define\([\s]*["\']{1}([A-Z_0-9]+)["\']{1}[\s]*,[\s]*(["\']{1})([^\\2]*)\\2[\s]*\)/U',
                $wpConfig,
                $matches
            )) {
                IntegrationException::throwException('Unable to extract values from wp-config.php');
            }
    
            $this->data[$storeId] = array_combine($matches[1], $matches[3]);
    
            if (preg_match_all(
                '/define\([\s]*["\']{1}([A-Z_0-9]+)["\']{1}[\s]*,[\s]*(true|false|[0-9]{1,})[\s]*\)/U',
                $wpConfig,
                $matches
            )) {
                $temp = array_combine($matches[1], $matches[2]);
    
                foreach ($temp as $k => $v) {
                    if ($v === 'true') {
                        $this->data[$storeId][$k] = true;
                    } elseif ($v === 'false') {
                        $this->data[$storeId][$k] = false;
                    } else {
                        $this->data[$storeId][$k] = $v;
                    }
                }
            }
    
            if (preg_match('/\$table_prefix[\s]*=[\s]*(["\']{1})([a-zA-Z0-9_]+)\\1/', $wpConfig, $match)) {
                $this->data[$storeId]['DB_TABLE_PREFIX'] = $match[2];
            } else {
                $this->data[$storeId]['DB_TABLE_PREFIX'] = 'wp_';
            }
        }
        
        if ($key === null) {
            return $this->data[$storeId];
        } elseif (isset($this->data[$storeId][$key])) {
            return $this->data[$storeId][$key];
        }
        
        return $default;
    }
}

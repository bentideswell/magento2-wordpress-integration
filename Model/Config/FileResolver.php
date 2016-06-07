<?php
/**
 * Application config file resolver
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FishPig\WordPress\Model\Config;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @param Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->moduleReader = $moduleReader;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
		$files = $this->moduleReader->getConfigurationFiles($filename)->toArray();
		
		foreach($files as $file => $xml) {
			if (strpos($file, 'FishPig') === false) {
				unset($files[$file]);
				$files[$file] = $xml;
			}
		}
		
		return $files;
    }
}

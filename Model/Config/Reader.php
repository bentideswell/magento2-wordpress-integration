<?php

namespace FishPig\WordPress\Model\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
	protected $_cachedConfig = null;
	
    protected $_idAttributes = [
		'/wordpress/database/tables/table' => 'id',
		'/wordpress/sidebar/widgets/widget' => 'id',
		'/wordpress/shortcodes/shortcode' => 'id',
    ];
    	
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Config\ConverterInterface $converter
     * @param \Genmato\TableXml\Model\Table\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \FishPig\WordPress\Model\Config\FileResolver $fileResolver,
        \Magento\Framework\Config\ConverterInterface $converter,
        \FishPig\WordPress\Model\Config\SchemaLocator $schemaLocator,
        \FishPig\WordPress\Model\Config\ValidationState $validationState,
        $fileName = 'wordpress.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
    
    /**
     * Load configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
	    if ($this->_cachedConfig === null) {
			$this->_cachedConfig = false;

		    if ($data = parent::read($scope)) {
			    $configData = $this->_fixConfigArray($data);;
			    
			    $this->_cachedConfig = isset($configData['wordpress']) ? $configData['wordpress'] : false;
			}
		}
		
	    return $this->_cachedConfig;
    }
    
	protected function _fixConfigArray($arr)
	{
	    if (is_array($arr)) {
		    if (count($arr) === 1 && isset($arr[0])) {
				$arr = $this->_fixConfigArray($arr[0]);
		    }
		    else {
			    foreach($arr as $key => $value) {
				    if (is_array($value)) {
					    $arr[$key] = $this->_fixConfigArray($value);
				    }
			    }
			}
	    }
	    
	    return $arr;
    }

    public function getValue($key, $scope = null)
    {
	    $data = $this->read($scope);
		
		if (strpos($key, '/') === false) {
			return isset($data[$key]) ? $data[$key] : false;
		}

		foreach(explode('/', $key) as $part) {
			if (!isset($data[$part]))	{
				return false;
			}
			
			$data = $data[$part];
		}
		
		return !empty($data) ? $data : false;
    }    
}

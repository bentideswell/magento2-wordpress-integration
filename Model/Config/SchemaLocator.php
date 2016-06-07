<?php

namespace FishPig\WordPress\Model\Config;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Get path to merged config schema
     *
     * @return string
     */
    public function getSchema()
    {
	    return null;
        return realpath(__DIR__ . '/../../etc/wordpress.xsd');
    }
    /**
     * Get path to pre file validation schema
     *
     * @return null
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
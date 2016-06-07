<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FishPig\WordPress\Model\Config;

/**
 * Config validation state interface.
 *
 * @api
 */
class ValidationState implements \Magento\Framework\Config\ValidationStateInterface
{
    /**
     * Retrieve current validation state
     *
     * @return boolean
     */
    public function isValidationRequired()
    {
	    return false;
    }
}

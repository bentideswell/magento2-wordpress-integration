<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Config;

use Magento\Framework\Serialize\SerializerInterface;

class Core extends \Magento\Framework\Config\Data
{   
    /**
     * @return array|false
     */
    public function getGlobalVariables()
    {
        return $this->get('globalVariables', []) ?? false;
    }
}

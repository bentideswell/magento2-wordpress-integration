<?php
/**
 *
 *
 */
namespace FishPig\WordPress\Model\Integration;

use \Exception;

class IntegrationException extends Exception
{
    /**
     * @return 
     */
    static public function throwException($message)
    {
        $class = get_called_class();

        throw new $class($message);
    }
}

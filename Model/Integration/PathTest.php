<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Integration;

use FishPig\WordPress\Model\DirectoryList;
use FishPig\WordPress\Model\Integration\IntegrationException;

class PathTest
{
    /**
     * @var 
     */
    protected $wpDirectoryList;

    /**
     *
     */
    public function __construct(DirectoryList $wpDirectoryList)
    {
        $this->wpDirectoryList = $wpDirectoryList;
    }

    /**
     * @return 
     */
    public function runTest()
    {
        if ($this->wpDirectoryList->isValidBasePath() === false) {
            IntegrationException::throwException(
                'Unable to find a WordPress installation at specified path.'
            );
        }

        return $this;
    }
}

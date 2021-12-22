<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class UserRepository extends \FishPig\WordPress\Model\Repository\ModelRepository
{
    /**
     * @param  string $name
     * @return \FishPig\WordPress\Model\User
     */
    public function getByNicename($name): \FishPig\WordPress\Model\User
    {
        return $this->getByField($name, 'user_nicename');
    }
}

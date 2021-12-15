<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\User;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_user_collection';
    protected $_eventObject = 'users';
}

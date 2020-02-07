<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel\User;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Meta\Collection\AbstractCollection
{

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'wordpress_user_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'users';

    public function _construct()
    {
        $this->_init('FishPig\WordPress\Model\User', 'FishPig\WordPress\Model\ResourceModel\User');
    }
}

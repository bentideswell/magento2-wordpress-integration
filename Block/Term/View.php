<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Term;

use \FishPig\WordPress\Model\Term;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    public function getEntity()
    {
        return $this->getTerm();
    }

    /**
     * Returns the current Wordpress category
     * This is just a wrapper for getCurrentCategory()
     *
     * @return \FishPig\WordPress\Model\Term
     */
    public function getTerm()
    {
        if (!$this->hasTerm()) {
            $this->setTerm($this->registry->registry(Term::ENTITY));
        }

        return $this->_getData('term');
    }

    /**
     * Generates and returns the collection of posts
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function _getPostCollection()
    {
        if ($this->getTerm()) {
            return $this->getTerm()->getPostCollection();
        }

        return false;
    }
}

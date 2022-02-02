<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Term;

use FishPig\WordPress\Model\Term;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     * @var Term
     */
    private $term = null;
    
    /**
     * @return Term
     */
    public function getTerm(): Term
    {
        if ($this->term === null) {
            $this->term = $this->registry->registry(Term::ENTITY) ?? false;
        }
        
        return $this->term;
    }

    /**
     * @param  Term $term
     * @return self
     */
    public function setTerm(Term $term): self
    {
        $this->term = $term;
        return $this;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function getBasePostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        return $this->getTerm()->getPostCollection();
    }
    
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->getTerm()->getDescription();
    }
    
    /**
     * @deprecated 3.0 use self::getTerm
     */
    public function getEntity()
    {
        return $this->getTerm();
    }

}

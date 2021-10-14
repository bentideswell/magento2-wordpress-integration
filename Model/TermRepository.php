<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class TermRepository
{
    /**
     * @const string
     */
    const FIELD_DEFAULT = 'term_id';

    /**
     * @param \FishPig\WordPress\Model\PostFactory $postFactory
     */
    public function __construct(
        \FishPig\WordPress\Model\TermFactory $objectFactory
    ) {
        $this->objectFactory = $objectFactory;
    }
    
    /**
     * @param  mixed $id
     * @param  string $taxonomy = null
     * @param  string $field 
     * @return \FishPig\WordPress\Model\Term
     */
    public function get($id, $taxonomy = null, $field = self::FIELD_DEFAULT): \FishPig\WordPress\Model\Term
    {
        if ($field === self::FIELD_DEFAULT) {
            if (isset($this->cache[(int)$id])) {
                return $this->cache[(int)$id];
            }
        } elseif ($this->cache) {
            foreach ($this->cache as $id => $object) {
                if ($object->getData($field) === $id) {
                    return $object;
                }
            }
        }
        
        $this->cache[$id] = false;
        
        $object = $this->objectFactory->create();
        
        if ($taxonomy !== null) {
            $object->setTaxonomy($taxonomy);
        }

        if (!$object->load($id, $field)->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __("The WordPress term (" . $field . '=' . $id . ") that was requested doesn't exist. Verify the term and try again.")
            );
        }
        
        return $this->cache[(int)$object->getId()] = $object;
    }
}
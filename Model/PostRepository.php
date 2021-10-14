<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class PostRepository
{
    /**
     * @const string
     */
    const FIELD_DEFAULT = 'ID';

    /**
     * @param \FishPig\WordPress\Model\PostFactory $postFactory
     */
    public function __construct(
        \FishPig\WordPress\Model\PostFactory $postFactory
    ) {
        $this->postFactory = $postFactory;
    }
    
    /**
     *
     */
    public function get($id, $type = null, $field = self::FIELD_DEFAULT)
    {
        if ($field === self::FIELD_DEFAULT) {
            if (isset($this->cache[(int)$id])) {
                return $this->cache[(int)$id];
            }
        } elseif ($this->cache) {
            foreach ($this->cache as $id => $post) {
                if ($post->getData($field) === $id) {
                    return $post;
                }
            }
        }
        
        $this->cache[$id] = false;
        
        $post = $this->postFactory->create();
        
        if ($type !== null) {
            $post->setPostType($type);
        }

        if (!$post->load($id, $field)->getId()) {
            throw new NoSuchEntityException(
                __("The WordPress post (" . $field . '=' . $id . ") that was requested doesn't exist. Verify the post and try again.")
            );
        }
        
        return $this->cache[(int)$post->getId()] = $post;
    }
}
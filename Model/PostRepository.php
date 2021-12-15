<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class PostRepository extends \FishPig\WordPress\Model\Repository\ModelRepository
{
    /**
     * @param  int $id
     * @param  array|string $types
     * @return FishPig\WordPress\Model\Post
     */
    public function getWithType($id, $types)
    {
        $post = $this->get($id);

        if (!in_array('*', (array)$types) && !in_array($post->getPostType(), (array)$types)) {
            throw new NoSuchEntityException(
                __(
                    'The WordPress post exits but failed the type check. ID is %1, type is %2',
                    $post->getId(),
                    $post->getPostType()
                )
            );
        }
        
        return $post;
    }
}

<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class TermRepository extends \FishPig\WordPress\Model\Repository\ModelRepository
{
    /**
     * @param  int $id
     * @param  array|string $taxonomies
     * @return FishPig\WordPress\Model\Term
     */
    public function getWithTaxonomy($id, $taxonomies)
    {
        $object = $this->get($id);
        
        if (!in_array($object->getTaxonomy(), (array)$taxonomies)) {
            throw new NoSuchEntityException(
                __(
                    'The WordPress term exits but failed the taxonomy check. ID is %1, taxonomy is %2',
                    $object->getId(),
                    $object->getTaxonomy()
                )
            );
        }
        
        return $object;
    }
}

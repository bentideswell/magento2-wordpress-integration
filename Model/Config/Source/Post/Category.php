<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Config\Source\Post;

class Category implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \FishPig\WordPress\Model\ResourceModel\Term\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \FishPig\WordPress\Model\ResourceModel\Term\CollectionFactory $collectionFactory
     */
    public function __construct(
        \FishPig\WordPress\Model\ResourceModel\Term\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => __('-- Please Select --')]];

        $collection = $this->collectionFactory->create()->addTaxonomyFilter('category');
        $collection->setOrderByName();

        $terms = [];
        $childMap = [];

        foreach ($collection as $term) {
            $termId = (int)$term->getId();
            $parentId = (int)$term->getParentId();
            $terms[$termId] = $term;
            $childMap[$parentId][] = $termId;
        }

        $this->appendOptions($options, $terms, $childMap, 0, 0);

        return $options;
    }

    /**
     * @param array $options
     * @param array $terms
     * @param array $childMap
     * @param int   $parentId
     * @param int   $depth
     * @return void
     */
    private function appendOptions(array &$options, array $terms, array $childMap, int $parentId, int $depth): void
    {
        if (!isset($childMap[$parentId])) {
            return;
        }

        $prefix = $depth > 0 ? str_repeat('-', $depth) . ' ' : '';

        foreach ($childMap[$parentId] as $termId) {
            $options[] = [
                'value' => $termId,
                'label' => $prefix . $terms[$termId]->getName()
            ];
            $this->appendOptions($options, $terms, $childMap, $termId, $depth + 1);
        }
    }
}

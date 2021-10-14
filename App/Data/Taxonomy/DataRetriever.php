<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Data\Taxonomy;

class DataRetriever implements \FishPig\WordPress\Api\Data\App\Data\Taxonomy\TaxonomyRetrieverInterface
{
    /**
     * @var
     */
    private $option;

    /**
     * @param  \FishPig\WordPress\App\Option $option
     */
    public function __construct(
        \FishPig\WordPress\App\Option $option
    ) {
        $this->option = $option;
    }

    /**
     * @return []
     */
    public function getData(): array
    {
        return [
            'category' => [
                'type' => 'category',
                'taxonomy_type' => 'category',
                'labels' => [
                    'name' => 'Categories',
                    'singular_name' => 'Category',
                ],
                'public' => true,
                'hierarchical' => true,
                'rewrite' => [
                    'hierarchical' => true,
                    'slug' => $this->option->get('category_base') ?? 'category',
                    'with_front' => true,
                ],
                '_builtin' => true,
            ],
            'post_tag' => [
                'type' => 'post_tag',
                'taxonomy_type' => 'post_tag',
                'labels' => [
                    'name' => 'Tags',
                    'singular_name' => 'Tag',
                ],
                'public' => true,
                'hierarchical' => false,
                'rewrite' => [
                    'slug' => $this->option->get('tag_base') ?? 'tag',
                    'with_front' => true,
                ],
                '_builtin' => true,
            ]
        ];
    }
}

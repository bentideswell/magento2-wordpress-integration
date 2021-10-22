<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Data\Taxonomy;

class DataRetriever implements \FishPig\WordPress\Api\App\Data\Taxonomy\TaxonomyRetrieverInterface
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
                    'slug' => $this->getBase('category_base', 'category'),
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
                    'slug' => $this->getBase('tag_base', 'tag'),
                    'with_front' => true,
                ],
                '_builtin' => true,
            ]
        ];
    }
    
    /**
     * @param  string $key
     * @param  string $default
     * @return string
     */
    private function getBase(string $key, string $default): string
    {
        if ($base = trim($this->option->get($key), '/')) {
            return $base;
        }
        
        return $default;
    }
}

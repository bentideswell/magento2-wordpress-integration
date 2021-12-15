<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\PostType;

class DataRetriever implements \FishPig\WordPress\Api\App\PostType\PostTypeRetrieverInterface
{
    /**
     * @var \FishPig\WordPress\App\Option
     */
    private $option;

    /**
     * @param \FishPig\WordPress\App\Option $option
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
            'post' => [
                'post_type' => 'post',
                'rewrite' => [
                    'slug' => $this->option->get('permalink_structure')
                ],
                'rest_base' => 'posts',
                'taxonomies' => ['category', 'post_tag'],
                '_builtin' => true,
                'public' => true,
                'labels' => [
                    'name' => 'Posts',
                    'singular_name' => 'Post',
                ]
            ],
            'page' => [
                'post_type' => 'page',
                'rewrite' => [
                    'slug' => '%postname%/',
                    'hierarchical' => true
                ],
                'rest_base' => 'pages',
                'hierarchical'  => true,
                'taxonomies' => [],
                '_builtin' => true,
                'public' => true,
                'labels' => [
                    'name' => 'Pages',
                    'singular_name' => 'Page',
                ]
            ]
        ];
    }
}

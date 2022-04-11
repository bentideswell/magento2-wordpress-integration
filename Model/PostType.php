<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\ViewableModelInterface;
use FishPig\WordPress\Api\Data\PostCollectionGeneratorInterface;

class PostType extends \Magento\Framework\DataObject implements ViewableModelInterface, PostCollectionGeneratorInterface
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_post_type';
    const CACHE_TAG = 'wordpress_post_type';

    /**
     * @var \FishPig\WordPress\Model\ResourceModel\PostType
     */
    private $_resource;
    
    /**
     * @param array $data = []
     */
    public function __construct(
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\PostType $resource,
        \FishPig\WordPress\Helper\FrontPage $frontPage,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        array $data = []
    ) {
        $this->url = $wpContext->getUrl();
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        $this->_resource = $resource;
        $this->frontPage = $frontPage;
        $this->taxonomyRepository = $taxonomyRepository;
        parent::__construct($data);
    }

    /**
     * Get the name of the post type
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('labels/name');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->getPostType() === 'post') {
            if ($this->isFrontPage()) {
                return $this->url->getHomeUrl();
            }
            
            if ($postsPage = $this->frontPage->getPostsPage()) {
                return $postsPage->getUrl();
            }
            
            return $this->url->getHomeUrl();
        }
        
        if (!$this->hasArchive()) {
            return '';
        }

        $urlPath = $this->getArchiveSlug() . ($this->permalinkHasTrainingSlash() ? '/' : '');
        
        return $this->withFront()
            ? $this->url->getHomeUrlWithFront($urlPath)
            : $this->url->getHomeUrl($urlPath);
    }
    
    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        return $this->postCollectionFactory->create()->addPostTypeFilter(
            $this->getPostType()
        );
    }
    
    /**
     * @return \FishPig\WordPress\Model\ResourceModel\PostType
     */
    public function getResource(): \FishPig\WordPress\Model\ResourceModel\PostType
    {
        return $this->_resource;
    }

    /**
     * Determine whether the permalink has a trailing slash
     *
     * @return bool
     */
    public function permalinkHasTrainingSlash()
    {
        return substr($this->getSlug(), -1) === '/' || substr($this->getPermalinkStructure(), -1) === '/';
    }
    
    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return (int)$this->_getData('public') === 1;
    }
    
    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return (int)$this->_getData('_builtin') === 1;
    }
    
    /**
     * @return bool
     */
    public function useGuidLinks(): bool
    {
        return trim($this->getData('rewrite/slug')) === '';
    }

    /**
     * @return bool
     */
    public function withFront(): bool
    {
        return (int)$this->getData('rewrite/with_front') === 1;
    }

    /**
     * @return bool
     */
    public function isFrontPage(): bool
    {
        return $this->getPostType() === 'post' && $this->frontPage->isFrontPageDefaultPostTypeArchive();
    }
    
    /**
     * @return string
     */
    public function getPermalinkStructure(): string
    {
        $structure = ltrim(str_replace('index.php/', '', ltrim($this->getData('rewrite/slug'), ' -/')), '/');

        if (!$this->isDefault() && strpos($structure, '%postname%') === false) {
            $structure = rtrim($structure, '/') . '/%postname%/';
        }

        if ($this->isHierarchical()) {
            $structure = str_replace('%postname%', '%postnames%', $structure);
        }

        if ($this->withFront() && ($front = $this->url->getFront())) {
            $structure = ltrim($front . '/' . $structure, '/');
        }

        return $structure;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->getData('rewrite/slug');
    }

    /**
     * @return bool
     */
    public function hasArchive(): bool
    {
        return $this->getHasArchive() && $this->getHasArchive() !== '0';
    }
    
    /**
     * Get the archive slug for the post type
     *
     * @return string
     */
    public function getArchiveSlug(): string
    {
        if (!$this->hasArchive()) {
            return '';
        }

        $slug = '';

        // phpcs:ignore -- empty if
        if (((string)$slug = $this->getHasArchive()) !== '1') {
            /**/
        } elseif ($slug = $this->getSlug()) {
            if (strpos($slug, '%') !== false) {
                $slug = trim(substr($slug, 0, strpos($slug, '%')), '%/');
            }
        }

        if (!$slug) {
            $slug = $this->getPostType();
        }

        return ltrim($slug, '/');
    }

    /**
     * @return string
     */
    public function getArchiveUrl(): string
    {
        return $this->getUrl();
    }

    /**
     * Determine whether $taxonomy is supported by the post type
     *
     * @param  string $taxonomy
     * @return bool
     */
    public function isTaxonomySupported($taxonomy)
    {
        return $this->getTaxonomies() ? in_array($taxonomy, $this->getTaxonomies()) : false;
    }

    /**
     * Get a taxonomy that is supported by the post type
     *
     * @return string
     */
    public function getAnySupportedTaxonomy($prioritise = [])
    {
        if (!is_array($prioritise)) {
            $prioritise = [$prioritise];
        }

        foreach ($prioritise as $type) {
            if ($this->isTaxonomySupported($type)) {
                return $this->taxonomyRepository->get($type);
            }
        }

        if ($taxonomies = $this->getTaxonomies()) {
            return $this->taxonomyRepository->get(array_shift($taxonomies));
        }

        return false;
    }

    /**
     * Determine whether this post type is hierarchical
     *
     * @return bool
     */
    public function isHierarchical(): bool
    {
        return (int)$this->getData('hierarchical') === 1;
    }

    /**
     * Get the hierarchical post name for a post
     * This is the same as %postname% but with all of the parent post names included
     *
     * @param  int $id
     * @return string|false
     */
    public function getHierarchicalPostName($id)
    {
        if ($routes = $this->getHierarchicalPostNames()) {
            return isset($routes[$id]) ? $routes[$id] : false;
        }

        return false;
    }

    /**
     * Get all routes (hierarchical)
     *
     * @return false|array
     */
    public function getAllRoutes()
    {
        return $this->getHierarchicalPostNames();
    }

    /**
     * Get an array of hierarchical post names
     *
     * @return false|array
     */
    public function getHierarchicalPostNames()
    {
        if (!$this->isHierarchical()) {
            return false;
        }
        
        return $this->getResource()->getHierarchicalPostNames($this);
    }

    /**
     * @return string
     */
    public function getPostType()
    {
        return $this->_getData('post_type') ? $this->_getData('post_type') : $this->_getData('name');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getPostType();
    }

    /**
     * @return string
     */
    public function getPluralName()
    {
        return $this->getData('labels/name');
    }

    /**
     * Determine whether post's of this type are included in the search
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return (int)$this->getData('exclude_from_search') === 0;
    }
}

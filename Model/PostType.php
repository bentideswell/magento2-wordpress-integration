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
        array $data = []
    ) {
        $this->url = $wpContext->getUrl();
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        $this->_resource = $resource;
        $this->frontPage = $frontPage;
        
        parent::__construct($data);
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
    public function isFrontPage(): bool
    {
        if ($this->getPostType() !== 'post') {
            return false;
        }

        if ($this->frontPage->isFrontPageDefaultPostTypeArchive()) {
            return true;
        }

        return false;
    }
    
    /**
     * Get the permalink structure as a string
     *
     * @return string
     */
    public function getPermalinkStructure()
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
     * @return bool
     */
    public function withFront(): bool
    {
        return (int)$this->getData('rewrite/with_front') === 1;
    }

    /**
     * Retrieve the permalink structure in array format
     *
     * @return false|array
     */
    public function getExplodedPermalinkStructure()
    {
        $structure = $this->getPermalinkStructure();
        $parts = preg_split("/(\/|-)/", $structure, -1, PREG_SPLIT_DELIM_CAPTURE);
        $structure = [];

        foreach ($parts as $part) {
            if ($result = preg_split("/(%[a-zA-Z0-9_]{1,}%)/", $part, -1, PREG_SPLIT_DELIM_CAPTURE)) {
                $results = array_filter(array_unique($result));

                foreach ($results as $result) {
                    array_push($structure, $result);
                }
            } else {
                $structure[] = $part;
            }
        }

        return $structure;
    }

    /**
     * Determine whether the permalink has a trailing slash
     *
     * @return bool
     */
    public function permalinkHasTrainingSlash()
    {
        return substr($this->getData('rewrite/slug'), -1) === '/' || substr($this->getPermalinkStructure(), -1) === '/';
    }

    /**
     * Retrieve the URL to the cpt page
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url->getUrl($this->getArchiveSlug() . '/');
    }

    /**
     * Get the archive slug for the post type
     *
     * @return string
     */
    public function getSlug()
    {
        $slug = $this->getData('rewrite/slug');

        if ($this->withFront()) {
            $slug = $this->getFront() . '/' . $slug;
        }

        return $slug;
    }

    /**
     * Get the archive slug for the post type
     *
     * @return string
     */
     
     
    /**
     * @return bool
     */
    public function hasArchive()
    {
        return $this->getHasArchive() && $this->getHasArchive() !== '0';
    }
    
    /**
     * Get the archive slug for the post type
     *
     * @return string
     */
    public function getArchiveSlug()
    {
        if (!$this->hasArchive()) {
            return false;
        }

        $slug = false;

        if (((string)$slug = $this->getHasArchive()) !== '1') {
            // Do nothing yet
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
     * Get the URL of the archive page
     *
     * @return string
     */
    public function getArchiveUrl(): string
    {
        if ($this->getPostType() !== 'post') {
            return $this->hasArchive() ? $this->url->getUrl($this->getArchiveSlug() . '/') : '';
        }
        
        if ($this->isFrontPage()) {
            return $this->url->getHomeUrl();
        }
        
        return $this->frontPage->getPostsPage()->getUrl();
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
                return $this->taxonomyManager->getTaxonomy($type);
            }
        }

        if ($taxonomies = $this->getTaxonomies()) {
            return $this->taxonomyManager->getTaxonomy(array_shift($taxonomies));
        }

        return false;
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

    /* ToDo: standardise getallroutes calls */
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
}

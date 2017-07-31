<?php
	
namespace FishPig\WordPress\Model\Post;

use \FishPig\WordPress\App;
use \FishPig\WordPress\Model\Post\Type\AbstractType as AbstractPostType;
use \FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Type extends AbstractPostType implements ViewableInterface
{
	/**
	 *
	**/
	const ENTITY = 'wordpress_post_type';

	/**
	 * Cache of URI's for hierarchical post types
	 *
	 * @var array static
	 */
	static $_uriCache = array();
    
	/**
	 * Determine whether post type uses GUID links
	 *
	 * @return bool
	 */
	public function useGuidLinks()
	{
		return trim($this->getData('rewrite/slug')) === '';
	}
	
	/**
	 * Determine whether the post type is a built-in type
	 *
	 * @return bool
	 */
	public function isDefault()
	{
		return (int)$this->_getData('_builtin') === 1;
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
		

		if ((int)$this->getData('rewrite/with_front') === 1) {
			$postPermalink = $this->_app->getPostType('post')->getPermalinkStructure();
			
			if (substr($postPermalink, 0, 1) !== '%') {
				$front = trim(substr($postPermalink, 0, strpos($postPermalink, '%')), '/');
				
				$structure = $front . '/' . $structure;
			}
		}

		return $structure;
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
		$structure = array();

		foreach($parts as $part) {
			if ($result = preg_split("/(%[a-zA-Z0-9_]{1,}%)/", $part, -1, PREG_SPLIT_DELIM_CAPTURE)) {
				$results = array_filter(array_unique($result));

				foreach($results as $result) {
					array_push($structure, $result);
				}
			}
			else {
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
		return $this->_app->getWpUrlBuilder()->getUrl($this->getArchiveSlug() . '/');
	}
	
	/**
	 * Retrieve the post collection for this post type
	 *
	 * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
	 */
	public function getPostCollection()
	{
		return $this->_factory->getFactory('Post')->create()->getCollection()->addPostTypeFilter($this->getPostType());
	}

	/**
	 * Get the archive slug for the post type
	 *
	 * @return string
	 */	
	public function getSlug()
	{
		return $this->getData('rewrite/slug');
	}
	
	/**
	 * Get the archive slug for the post type
	 *
	 * @return string
	 */
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
		
		if (((string)$slug = $this->getHasArchive()) !== '1') {
			return $slug;
		}
		
		if ($slug = $this->getSlug()) {
			if (strpos($slug, '%') !== false) {
				$slug = trim(substr($slug, 0, strpos($slug, '%')), '%/');
			}
			
			if ($slug) {
				return $slug;
			}
		}
		
		return $this->getPostType();
	}
	
	/**
	 * Get the URL of the archive page
	 *
	 * @return string
	 */
	public function getArchiveUrl()
	{
		return $this->hasArchive()
			? $this->_wpUrlBuilder->getUrl($this->getArchiveSlug() . '/')
			: '';
	}

	/**
	 * Determine whether $taxonomy is supported by the post type
	 *
	 * @param string $taxonomy
	 * @return bool
	 */
	public function isTaxonomySupported($taxonomy)
	{
		return $this->getTaxonomies()
			? in_array($taxonomy, $this->getTaxonomies())
			: false;
	}
	
	/**
	 * Get a taxonomy that is supported by the post type
	 *
	 * @return string
	 */
	public function getAnySupportedTaxonomy($prioritise = array())
	{
		if (!is_array($prioritise)) {
			$prioritise = array($prioritise);
		}
		
		foreach($prioritise as $type) {
			if ($this->isTaxonomySupported($type)) {
				return $this->_app->getTaxonomy($type);
			}
		}
		
		if ($taxonomies = $this->getTaxonomies()) {
			return $this->_app->getTaxonomy(array_shift($taxonomies));
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
	public function isHierarchical()
	{
		return (int)$this->getData('hierarchical') === 1;
	}
	
	/**
	 * Get the hierarchical post name for a post
	 * This is the same as %postname% but with all of the parent post names included
	 *
	 * @param int $id
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
		
		if (isset(self::$_uriCache[$this->getPostType()])) {
			return self::$_uriCache[$this->getPostType()];
		}
		
		if (!($db = $this->_resource->getConnection())) {
			return false;
		}

		$select = $db->select()
			->from(array('term' => $this->_resource->getTable('wordpress_post')), array(
				'id' => 'ID',
				'url_key' =>  'post_name', 
				'parent' => 'post_parent'
			))
			->where('post_type=?', $this->getPostType())
			->where('post_status=?', 'publish');
				
		self::$_uriCache[$this->getPostType()] = $this->_generateRoutesFromArray($db->fetchAll($select));
		
		return self::$_uriCache[$this->getPostType()];
	}
	
	/**
	 * @return bool
	 */
	public function hasArchive()
	{
		return $this->getHasArchive() && $this->getHasArchive() !== '0';
	}
	
	/**
	 * @return string
	**/
	public function getPostType()
	{
		return $this->_getData('post_type') ? $this->_getData('post_type') : $this->_getData('name');
	}

	/**
	 * @return string
	**/	
	public function getPluralName()
	{
		return $this->getData('labels/name');
	}
}

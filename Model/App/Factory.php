<?php
/**
 *
**/
namespace FishPig\WordPress\Model\App;

/**
 *
**/
class Factory
{
	/**
	 *
	**/
	protected $_factories = null;
	
	/**
	 *
	**/
	public function __construct(
		\FishPig\WordPress\Model\ArchiveFactory $archiveFactory,
		\FishPig\WordPress\Model\HomepageFactory $homepageFactory,
		\FishPig\WordPress\Model\ImageFactory $imageFactory,
		\FishPig\WordPress\Model\MenuFactory $menuFactory,
		\FishPig\WordPress\Model\Menu\ItemFactory $menuItemFactory,
		\FishPig\WordPress\Model\PostFactory $postFactory,
		\FishPig\WordPress\Model\Post\CommentFactory $postCommentFactory,
		\FishPig\WordPress\Model\Post\TypeFactory $postTypeFactory,
		\FishPig\WordPress\Model\SearchFactory $searchFactory,
		\FishPig\WordPress\Model\TermFactory $termFactory,
		\FishPig\WordPress\Model\Term\TaxonomyFactory $termTaxonomyFactory,
		\FishPig\WordPress\Model\UserFactory $userFactory
	) {
		$this->_factories = array(
			'Archive' => $archiveFactory,
			'Homepage' => $homepageFactory,
			'Image' => $imageFactory,
			'Menu' => $menuFactory,
			'Menu\Item' => $menuItemFactory, 
			'Post' => $postFactory,
			'Post\Comment' => $postCommentFactory,
			'Post\Type' => $postTypeFactory,
			'Search' => $searchFactory,
			'Term' => $termFactory,
			'Term\Taxonomy' => $termTaxonomyFactory,
			'User' => $userFactory,
		);
	}
	
	/**
	 *
	**/
	public function getFactory($class)
	{
		if (strpos($class, __NAMESPACE__) === 0) {
			$class = substr($class, strlen(__NAMESPACE__) + 1);
		}

		return isset($this->_factories[$class])
			? $this->_factories[$class]
			: false;
	}
}

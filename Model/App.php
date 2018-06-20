<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

use \FishPig\WordPress\Model\App\Integration\Exception as IntegrationException;
use \FishPig\WordPress\Model\Config;
use \FishPig\WordPress\Model\App\ResourceConnection;
use \FishPig\WordPress\Model\App\Url as WpUrlBuilder;
use \FishPig\WordPress\Model\App\Factory as WpFactory;
use \FishPig\WordPress\Helper\Theme as ThemeHelper;

class App
{   
	/*
	 * @var bool
	 */
	protected $state = null;
	
	/*
	 * @var string|false
	 */
	protected $path = null;
	
	/*
	 * @var ResourceConnection
	 */
	protected $resourceConnection;
	
	/*
	 * @var FishPig\WordPress\Model\App\Integration\Exception
	 */
	protected $exception = false;
	
	/*
	 * @var array
	 */
	protected $postTypes = null;
	
	/*
	 * @var array
	 */
	protected $taxonomies = null;

	/*
	 * Array of the definitions from wp-config.php
	 *
	 * @var array|false
	 */
	protected $wpconfig = null;

	/*
	 * @var FishPig\WordPress\Model\Config
	 */
	protected $config;
	
	/*
	 * @var FishPig\WordPress\Model\App\Factory
	 */
	protected $factory;
	
	/*
	 * @var FishPig\WordPress\Model\App\Url
	 */
	protected $wpUrlBuilder;
	
	/*
	 * @var FishPig\WordPress\Model\App\Url
	 */
	protected $themeHelper;

	/*
	 *
	 */
	public function __construct(Config $config, ResourceConnection $resourceConnection, WpUrlBuilder $urlBuilder, WpFactory $factory, ThemeHelper $themeHelper)
	{
		$this->config = $config;
		$this->resourceConnection = $resourceConnection;
		$this->wpUrlBuilder = $urlBuilder;
		$this->factory = $factory;
		$this->themeHelper = $themeHelper;
	}
	
	/*
	 *
	 *
	 * @return void
	 */
	public function init()
	{
		return $this->_init();
	}
	
	/*
	 * Initialize the connection to WordPress
	 *
	 * @return $this
	 */
	protected function _init()
	{
		if (!is_null($this->state)) {
			return $this;
		}

		$this->state = false;

		try {
			// Check that the path to WordPress is valid
			if ($this->getPath() === false) {
				throw new \Exception('Unable to find a WordPress installation at specified path.');
			}
			
			// Connect to the WordPress database
//			$this->_initResource();
			
			// This will load the wp-config.php values
			$this->getWpConfigValue();
			
			// Define the WP config values globally as WP does
			$this->_defineWpConfigValues();
			
			// Use the wp-config.php values to connect to the DB
			$this->_initResource();
			
			// Check that the integration is successful
			$this->_validateIntegration();
			
			if ($this->isThemeIntegrated()) {
				$this->themeHelper->setPath($this->getPath())->validate();
			}
			
			// Plugins can use this to check other things
			$this->performOtherChecks();

			// Mark the state as true. This means all is well
			$this->state = true;
		}
		catch (\Exception $e) {
			$this->exception = $e;
			$this->state = false;
		}
		
		return $this;
	}

   /*
	 * Get the absolute path to the WordPress installation
	 *
	 * @return false|string
	 */
  public function getPath()
  {
		if (!is_null($this->path)) {
			return $this->path;
		}
		
		$this->path = false;
		
		if (!($path = trim($this->getConfig()->getStoreConfigValue('wordpress/setup/path')))) {
			return $this->path;
		}
		
		if (substr($path, 0, 1) !== '/') {
			if (is_dir(BP . '/' . $path)) {
				$path = BP . '/' . $path;
			}
			else if (is_dir(BP . '/pub/' . $path)) {
				$path = BP . '/pub/' . $path;
			}
		}
		
		if (!is_dir($path) || !is_file($path . '/wp-config.php')) {
			return $this->path;
		}
		
		return $this->path = $path;
  }
    
	/*
	 * Get the wp-config.php definitions
	 *
	 * @param string|null $key = null
	 * @return mixed
	 */
	public function getWpConfigValue($key = null)
	{
		if (is_null($this->wpconfig)) {
			$wpConfig = file_get_contents($this->getPath() . '/wp-config.php');

			# Cleanup comments
			$wpConfig = str_replace("\n", "\n\n", $wpConfig);
			$wpConfig = preg_replace('/\n\#[^\n]{1,}\n/', "\n", $wpConfig);
			$wpConfig = preg_replace('/\n\\/\/[^\n]{1,}\n/', "\n", $wpConfig);
			$wpConfig = preg_replace('/\n\/\*.*\*\//Us', "\n", $wpConfig);

			if (!preg_match_all('/define\([\s]*["\']{1}([A-Z_0-9]+)["\']{1}[\s]*,[\s]*(["\']{1})([^\\2]*)\\2[\s]*\)/U', $wpConfig, $matches)) {
				throw new \Exception('Unable to extract values from wp-config.php');
			}

			$this->wpconfig = array_combine($matches[1], $matches[3]);
			
			if (preg_match_all('/define\([\s]*["\']{1}([A-Z_0-9]+)["\']{1}[\s]*,[\s]*(true|false|[0-9]{1,})[\s]*\)/U', $wpConfig, $matches)) {			
				$temp = array_combine($matches[1], $matches[2]);
				
				foreach($temp as $k => $v) {
					if ($v === 'true') {
						$this->wpconfig[$k] = true;
					}
					else if ($v === 'false') {
						$this->wpconfig[$k] = false;
					}
					else {
						$this->wpconfig[$k] = $v;
					}
				}
			}

			if (preg_match('/\$table_prefix[\s]*=[\s]*(["\']{1})([a-zA-Z0-9_]+)\\1/', $wpConfig, $match)) {
				$this->wpconfig['DB_TABLE_PREFIX'] = $match[2];
			}
			else {
				$this->wpconfig['DB_TABLE_PREFIX'] = 'wp_';
			}
		}

		if (is_null($key)) {
			return $this->wpconfig;
		}
		
		return isset($this->wpconfig[$key]) ? $this->wpconfig[$key] : false;
	}
	
	/*
	 * Define the WP Config definitions 
	 *
	 * @return $this
	 */
	protected function _defineWpConfigValues()
	{
		foreach($this->getWpConfigValue() as $key => $value) {
			$key = 'FISHPIG_' . $key;
			
			if (!defined($key))	{
				define($key, $value);
			}
		}
		
		return $this;
	}
	
	/*
	 * Get the database connection
	 *
	 * @return false|Magento\Framework\DB\Adapter\Pdo\Mysql
	 */
	protected function _initResource()
	{
		if (!$this->resourceConnection->isConnected()) {
			$this->resourceConnection->setTablePrefix($this->getWpConfigValue('DB_TABLE_PREFIX'))
				->setMappingData(array(
					'before_connect' => $this->getConfig()->getDbTableMapping('before_connect'),
					'after_connect' => $this->getConfig()->getDbTableMapping('after_connect'),
				))
				->connect(array(
	        'host' => $this->getWpConfigValue('DB_HOST'),
	        'dbname' => $this->getWpConfigValue('DB_NAME'),
	        'username' => $this->getWpConfigValue('DB_USER'),
	        'password' => $this->getWpConfigValue('DB_PASSWORD'),
	        'active' => '1',	
				)
			);
		}
		
		return $this;
	}
	
	/*
	 * Check that the WP settings allow for integration
	 *
	 * @return bool
	 */
	protected function _validateIntegration()
	{
		if (!$this->isThemeIntegrated()) {
			return $this;
		}

		$magentoUrl = $this->wpUrlBuilder->getMagentoUrl();

		if ($this->wpUrlBuilder->getHomeUrl() === $this->wpUrlBuilder->getSiteurl()) {
			IntegrationException::throwException(
				sprintf('Your WordPress Home URL matches your Site URL (%s).<br/>Your SiteURL should be the WordPress installation URL. The Home URL should be the integrated blog URL.', $this->wpUrlBuilder->getSiteurl())
			);
		}

		if ($this->isRoot()) {
			if ($this->wpUrlBuilder->getHomeUrl() !== $magentoUrl) {
				IntegrationException::throwException(
					sprintf('Your home URL is incorrect and should match your Magento URL. Change to. %s', $magentoUrl)
				);
			}
		}
		else {
			if (strpos($this->wpUrlBuilder->getHomeUrl(), $magentoUrl) !== 0) {
				IntegrationException::throwException(
					sprintf('Your home URL (%s) is invalid as it does not start with the Magento base URL (%s).', $this->wpUrlBuilder->getHomeUrl(), $magentoUrl)
				);
			}
			
			if ($this->wpUrlBuilder->getHomeUrl() === $magentoUrl) {
				IntegrationException::throwException('Your WordPress Home URL matches your Magento URL. Try changing your Home URL to something like ' . $magentoUrl . '/blog');
			}
		}
		
		return $this;
	}
	
    /*
	 * Get all of the post types
	 *
	 * @return false|array
	 */
	public function getPostTypes()
	{
		return $this->getPostType();
	}
	
    /*
	 * Get a single post type by the type or get an array of all post types
	 * This method also retrieves the post type data
	 *
	 * @param null|string $key
	 * @return false|array
	 */
	public function getPostType($key = null)
	{
		$this->_init();

		if (is_null($this->postTypes)) {
			$this->postTypes = $this->getAllPostTypes();
		}
		
		if (is_null($key)) {
			return $this->postTypes;
		}
		
		return isset($this->postTypes[$key]) ? $this->postTypes[$key]: false;
	}
	
	public function getAllPostTypes()
	{
		$postTypes = array();
		
		$postTypes = array(
			'post' => $this->factory->getFactory('Post\Type')->create(),
			'page' => $this->factory->getFactory('Post\Type')->create(),
		);
		
		$postTypes['post']->addData(array(
			'post_type' => 'post',
			'rewrite' => array('slug' => $this->getConfig()->getOption('permalink_structure')),
			'taxonomies' => array('category', 'post_tag'),
			'_builtin' => true,
		));
		
		$postTypes['page']->addData(array(
			'post_type' => 'page',
			'rewrite' => array('slug' => '%postname%/'),
			'hierarchical' => true,
			'taxonomies' => array(),
			'_builtin' => true,
		));
		
		return $postTypes;
	}
	
    /*
	 * Get a single taxonomy by the type or get an array of all taxonomies
	 *
	 * @param null|string $key
	 * @return false|array
	 */
	public function getTaxonomy($key = null)
	{
		$this->_init();
		
		if (is_null($this->taxonomies)) {
			$this->taxonomies = $this->getAllTaxonomies();
			
			foreach($this->taxonomies as $tax) {
				$tax->getSlug();
			}
		}
		
		if (is_null($key)) {
			return $this->taxonomies;
		}
		
		return isset($this->taxonomies[$key]) ? $this->taxonomies[$key] : false;
	}
	
	/*
	 * Get all of the taxonomies
	 *
	 * @return array
	 */
	public function getAllTaxonomies()
	{
		$this->_init();

		$blogPrefix = $this->isMultisite() && $this->getConfig()->getBlogId() === 1;
		
		$bases = array(
			'category' => $this->getConfig()->getOption('category_base') ? $this->getConfig()->getOption('category_base') : 'category',
			'post_tag' => $this->getConfig()->getOption('tag_base') ? $this->getConfig()->getOption('tag_base') : 'tag',
		);

		foreach($bases as $baseType => $base) {
			if ($blogPrefix && $base && strpos($base, '/blog') === 0) {
				$bases[$baseType] = substr($base, strlen('/blog'));	
			}
		}

		$taxonomies = array(
			'category' => $this->factory->getFactory('Term\Taxonomy')->create(),
			'post_tag' => $this->factory->getFactory('Term\Taxonomy')->create()
		);
		
		$taxonomies['category']->addData(array(
			'type' => 'category',
			'taxonomy_type' => 'category',
			'labels' => array(
				'name' => 'Categories',
				'singular_name' => 'Category',
			),
			'public' => true,
			'hierarchical' => true,
			'rewrite' => array(
				'hierarchical' => true,
				'slug' => $bases['category'],
			),
			'_builtin' => true,
		));
		
		$taxonomies['post_tag']->addData(array(
			'type' => 'post_tag',
			'taxonomy_type' => 'post_tag',
			'labels' => array(
				'name' => 'Tags',
				'singular_name' => 'Tag',
			),
			'public' => true,
			'hierarchical' => false,
			'rewrite' => array(
				'slug' => $bases['post_tag'],
			),
			'_builtin' => true,
		));

		return $taxonomies;
	}
	
	/*
	 * Get all of the taxonomies
	 *
	 * @return array
	 */
	public function getTaxonomies()
	{
		return $this->getTaxonomy();
	}
    
  /*
	 * Determine whether the integration is usable
	 *
	 * @return bool
	 */
  public function canRun()
  {
		$this->_init();
		
	  return $this->state === true;
  }
    
	/*
	 * Get the exception that occured during self::_init if it occured
	 *
	 * @return false|FishPig\WordPress\Model\App\Integration\Exception
	 */
	public function getException()
	{
		return $this->exception;
	}
	
	/*
	 * Get the config object
	 *
	 * @return \FishPig\WordPress\Model\Config
	 */
	public function getConfig()
	{
		$this->_init();
		
		return $this->config;
	}
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getHomepagePageId()
	{
		if ($this->getConfig()->getOption('show_on_front') === 'page') {
			if ($pageId = $this->getConfig()->getOption('page_on_front')) {
				return $pageId;
			}
		}
		
		return false;
	}
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getBlogPageId()
	{
		if ($this->config->getOption('show_on_front') === 'page') {
			if ($pageId = $this->config->getOption('page_for_posts')) {
				return $pageId;
			}
		}
		
		return false;
	}
	
	/*
	 *
	 *
	 * @return bool
	 */
	public function isMultisite()
	{
		return false;
	}
	
	/*
	 *
	 *
	 * @return bool
	 */
	public function isRoot()
	{
		return false;
	}

	/*
	 * Can be implemented by plugins to carry out integration tests
	 *
	 * @return bool
	 */
	public function performOtherChecks()
	{
		return true;
	}
	
	/*
	 * 
	 *
	 * @return \FishPig\WordPress\Model\App\ResourceConnection
	 */
	public function getResourceConnection()
	{
		$this->_init();
		
		return $this->resourceConnection;
	}
	
	/*
	 * 
	 *
	 * @return \FishPig\WordPress\Model\App\Url
	 */
	public function getWpUrlBuilder()
	{
		$this->_init();
		
		return $this->wpUrlBuilder;
	}
	
	/*
	 * @return \FishPig\WordPress\Model\App\Factory
	 */
	public function getFactory()
	{
		return $this->factory;
	}
	
	/*
	 *
	 *
	 * @return false
	 */
	public function getCoreHelper()
	{
		return false;
	}
	
	/*
	 *
	 *
	 * @return bool
	 */
	public function isThemeIntegrated()
	{
		return $this->getConfig()->getStoreConfigFlag('wordpress/setup/theme_integration');
	}
}

<?php
/*
 * @url http://fishpig.co.uk/
*/


class Fishpig_M2_PostTypesAndTaxonimes
{
	/**
	 * An array of post types to ignore
	 *
	 * @var array
	 */
	protected $_postTypesToIgnore = array(
		'attachment',
		'nav_menu_item',
		'revision',
	);
	
	/**
	 * An array of post types to ignore
	 *
	 * @var array
	 */
	protected $_taxonomiesToIgnore = array(
		'nav_menu',
		'link_category',
		'post_format',
	);
	
	/**
	 * Array containing post type data
	 *
	 * @var array
	 */
	static protected $_postTypeData = array();
	
	/**
	 * Array containing post type data
	 *
	 * @var array
	 */
	static protected $_taxonomyData = array();
	
	/**
	  * Generate post type data
	  *
	  * @return array
	  */
	public function getPostTypeData()
	{
		if (isset(self::$_postTypeData[get_current_blog_id()])) {
			return self::$_postTypeData[get_current_blog_id()];
		}

		// Include plugin.php so we can check whether WPPermastructure is active
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		$wpPermastructureActive = is_plugin_active('wp-permastructure/wp-permastructure.php');

		$customPostTypes = get_post_types(array('_builtin' => false, 'public' => true), 'objects');

		if (!$customPostTypes) {
			return self::$_postTypeData[get_current_blog_id()] = false;
		}
		
		$postTypeData = array_merge(
			$customPostTypes, get_post_types(array('_builtin' => true), 'objects')
		);

		foreach($postTypeData as $type => $data) {
			if (in_array($type, $this->_postTypesToIgnore)) {
				unset($postTypeData[$type]);
				continue;
			}

			$data = json_decode(json_encode($data), true);

			if ($type === 'post') {
				$data['rewrite'] = array(
					'slug' => get_option('permalink_structure')
				);
				
				$data['taxonomies'] = array_merge($data['taxonomies'], array('category', 'post_tag'));
			}
			else if ($type === 'page') {
				$data['rewrite'] = array(
					'slug' => '%postname%/',
				);				
			}
			else if ($wpPermastructureActive && $wpPermastructure = get_option($type . '_permalink_structure')) {
				$data['rewrite']['slug'] = $wpPermastructure;
			}

			// Convert any stdClass instances to an array
			$postTypeData[$type] = $data;
		}	

		return self::$_postTypeData[get_current_blog_id()] = $postTypeData;
	}

	/**
	  * Generate taxonomy data
	  *
	  * @return array
	  */	
	public function getTaxonomyData()
	{
		if (isset(self::$_taxonomyData[get_current_blog_id()])) {
			return self::$_taxonomyData[get_current_blog_id()];
		}

		if (!($customTaxonomies = get_taxonomies(array('_builtin' => false), 'objects'))) {
			return self::$_taxonomyData[get_current_blog_id()] =  false;
		}
		
		$taxonomyData = array_merge(
			$customTaxonomies, get_taxonomies(array('_builtin' => true), 'objects')
		);

		$blogPrefix = is_multisite() && !is_subdomain_install() && is_main_site();

		foreach($taxonomyData as $taxonomy => $data) {
			if (in_array($taxonomy, $this->_taxonomiesToIgnore)) {
				unset($taxonomyData[$taxonomy]);
				continue;
			}

			$data = json_decode(json_encode($data), true);

			if ($blogPrefix && isset($data['rewrite']) && isset($data['rewrite']['slug'])) {
				if (strpos($data['rewrite']['slug'], 'blog/') === 0) {
					$data['rewrite']['slug'] = substr($data['rewrite']['slug'], strlen('blog/'));
				}
			}
			
			$taxonomyData[$taxonomy] = $data;
		}

		return self::$_taxonomyData[get_current_blog_id()] = $taxonomyData;
	}
	
	/**
	 * Method that handles generating post type data
	 * and creating a file that Magento can use
	 *
	 * @return $this
	 */
	public function run()
	{
		update_option('fishpig_posttypes', json_encode($this->getPostTypeData()));
		update_option('fishpig_taxonomies', json_encode($this->getTaxonomyData()));
		
		return $this;
	}
}

if (function_exists('add_action')) {
	/**
	 * Setup object
	 *
	 */
	$fpM2PTT = new Fishpig_M2_PostTypesAndTaxonimes();
	
	/**
	 * Register action
	 *
	 */
	add_action('init', array($fpM2PTT, 'run'), 9999);
}

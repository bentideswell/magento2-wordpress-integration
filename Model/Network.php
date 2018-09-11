<?php
/*
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model;

class Network
{
	/*
	 * Determine whether the Network is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return false;
	}

	/*
	 * Get the site ID
	 *
	 * @return int
	 */
	public function getSiteId()
	{
		return 1;
	}

	/*
	 * Get the blog ID
	 *
	 * @return int
	 */
	public function getBlogId()
	{
		return 1;
	}

	/*
	 *
	 *
	 * return false
	 */
	public function getSiteAndBlogObjects()
	{
		return false;
	}
	
	/*
	 *
	 *
	 *
	 * @return false
	 */	
	public function getBlogTableValue($key)
	{		
		return false;
	}
	
	/*
	 *
	 *
	 *
	 * @return false
	 */
	public function getNetworkTables()
	{
		return false;
	}
}

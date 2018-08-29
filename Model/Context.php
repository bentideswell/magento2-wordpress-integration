<?php
/*
 * @category  Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */

namespace FishPig\WordPress\Model;

/* Parent Class */
use FishPig\WordPress\Model\Context\AbstractContext;

/* Constructor Args */
use FishPig\WordPress\Model\Url;
use FishPig\WordPress\Helper\View as ViewHelper;
/* End of Constructor Args */

class Context extends AbstractContext
{	
	/*
	 *
	 */
	public function __construct(Url $url, ViewHelper $viewHelper)
	{
	  $this->addObject($url, 'url');
	  $this->addObject($viewHelper, 'viewHelper');
	}
}

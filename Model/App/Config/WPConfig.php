<?php
/*
 *
 */
namespace FishPig\WordPress\Model\App\Config;

use FishPig\WordPress\Model\App;

class WPConfig
{   
	protected $app;
	protected $path;
	
	public function __construct(App $app)
	{
		$this->app  = $app;
		$this->path = $app->getPath();
		
		if ($this->path) {

			echo __METHOD__;exit;
		}
	}
	
}

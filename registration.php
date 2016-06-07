<?php
	
	use \Magento\Framework\Component\ComponentRegistrar;
	
	ComponentRegistrar::register(ComponentRegistrar::MODULE, 'FishPig_WordPress', __DIR__);

	return;
	/**
	  * Register add-on modules that are installed
	  * Eventually move this to a config.php file that gets cached to speed things up
	 **/
	$ds = DIRECTORY_SEPARATOR;	
	$addonPath =  __DIR__ . $ds . 'Addon';	

	if (is_dir($addonPath)) {
		if (($files = scandir($addonPath)) !== false) {
			foreach($files as $module) {
				$fullPath = $addonPath . $ds . $module;
				$moduleFile = $fullPath . $ds . 'etc' . $ds . 'module.xml';
				
				if (trim($module, '.') === '' || !is_dir($fullPath) || !is_file($moduleFile)) {
					continue;
				}

				ComponentRegistrar::register(ComponentRegistrar::MODULE, 'FishPig_WordPress_Addon_' . $module, $fullPath);
			}
		}
	}

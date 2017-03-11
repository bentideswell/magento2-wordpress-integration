<?php
	
	use \Magento\Framework\Component\ComponentRegistrar;

	$vendor = 'FishPig';
	$module = 'WordPress';
	
	ComponentRegistrar::register(
		ComponentRegistrar::MODULE,
		$vendor . '_' . $module,
		__DIR__
	);
	
	/*
	 * Protection against installing module in incorrect directory
	 * Too many emails with module installed in app/code/FishPig/Wordpress (p should be P)
	**/
	$currentLocation = __DIR__;
	
	if (strpos($currentLocation, 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR) !== false) {
		$correctLocation = dirname(dirname($currentLocation)) . DIRECTORY_SEPARATOR . $vendor . DIRECTORY_SEPARATOR . $module;
		$relativeLocation = $vendor . DIRECTORY_SEPARATOR . $module;
	
		throw new \Exception(sprintf(
			"%s_%s is installed in the wrong folder. The module is currently in the folder %s but should be in %s. Please ensure the correct capitalisation of the module name (%s).",
			$vendor,
			$module,
			$currentLocation,
			$correctLocation,
			$relativeLocation
		));	
	}

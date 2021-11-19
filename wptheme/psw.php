<?php
/**
 *
 */
define('FISHPIG_PSW_BASE_PATH_RELATIVE', 'fishpig/js');
define('FISHPIG_PSW_BASE_PATH', ABSPATH . FISHPIG_PSW_BASE_PATH_RELATIVE);

// Clear JS when an upgrade happens
foreach (['upgrader_process_complete', 'activated_plugin'] as $hook) {
    add_action($hook, function() {
        $newName = FISHPIG_PSW_BASE_PATH . '-' . date('YmdHis') . rand(1, 999999);

        if (rename(FISHPIG_PSW_BASE_PATH, $newName)) {
            fishpig_psw_rrmdir($newName);
        }
    });
}

// Allow dynamic JS process to run
fishpig_psw_handle_no_amd_js();

/**
 *
 */
/**
 * Delete a directory (that contains files)
 *
 * @param string $dir
 * @return void
 */
function fishpig_psw_rrmdir($dir) {
	$files = array_reverse(fishpig_psw_rscandir($dir));

	if (count($files) > 0) {
		foreach($files as $file) {
			if (is_file($file)) {
				unlink($file);
			}	
			else if (is_dir($file)) {
				rmdir($file);
			}
		}
	}
	
	rmdir($dir);
}

/**
 * Scan $dir and return all directories and files in an array
 *
 * @param string $dir
 * @param bool $reverse = false
 * @return array
 */
function fishpig_psw_rscandir($dir) {
	$files = array();
	
	foreach(scandir($dir) as $file) {
		if (trim($file, '.') === '') {
			continue;
		}
		
		$tmp = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
		$files[] = $tmp;

		if (is_dir($tmp)) {
			$files = array_merge($files, fishpig_psw_rscandir($tmp));
		}
	}

	return $files;
}

/**
 *
 */
function fishpig_psw_handle_no_amd_js()
{
    $requestUri = !empty($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI']) : '';
    $noAmdPrefix = '/' . FISHPIG_PSW_BASE_PATH_RELATIVE . '/';

    if (($pos = strpos($requestUri, $noAmdPrefix)) !== false) {
        $relativeJsSourceFile = substr($requestUri, $pos + strlen($noAmdPrefix));
    
        if (($pos = strpos($relativeJsSourceFile, '?')) !== false) {
            $relativeJsSourceFile = substr($relativeJsSourceFile, 0, $pos);
        }
        
        if (substr($relativeJsSourceFile, -3) !== '.js') {
            return '';
        }
        
        $jsSourceFile = realpath(ABSPATH . $relativeJsSourceFile);
        
        if (!$jsSourceFile || strpos($jsSourceFile, ABSPATH) !== 0) {
            return '';
        }
        
        $data = file_get_contents($jsSourceFile);
        $data = str_replace('define.amd',     'define.zyx', $data);
        $data = str_replace('typeof exports', 'typeof exportssdfsdfsdf', $data);

        $jsTargetFile = ABSPATH . ltrim($noAmdPrefix, '/') . $relativeJsSourceFile;
        $jsTargetPath = dirname($jsTargetFile);
        
        if (!is_dir($jsTargetPath)) {
            mkdir($jsTargetPath, 0755, true);
        }
        
        if (is_dir($jsTargetPath)) {
            file_put_contents($jsTargetFile, $data);
        }

        header('Content-Type: application/javascript');
        echo $data;
        exit;
    }
}

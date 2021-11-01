<?php
/**
 *
 */
fishpig_psw_handle_no_amd_js();

/**
 *
 */
function fishpig_psw_handle_no_amd_js()
{
    $requestUri = !empty($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI']) : '';
    $noAmdPrefix = '/fishpig/js/';

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

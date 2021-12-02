<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

class PackageDeployer
{
    /**
     * @return void
     */
    public function deploy(string $packageFile, string $wpPath): void
    {
        if (!is_file($packageFile)) {
            throw new \Exception($packageFile . ' does not exist.');
        }
        
        if (!is_dir($wpPath)) {
            throw new \Exception('WordPress path (' . $wpPath . ') is invalid.');
        }

        $wpThemePath = $wpPath . '/wp-content/themes';
        
        if (!is_dir($wpThemePath)) {
            throw new \Exception('Unable to find ' . $wpThemePath);
        }
        
        $fishPigThemePath = $wpThemePath . '/fishpig';
        
        if (is_dir($fishPigThemePath)) {
            $tempFishPigThemePath = $fishPigThemePath . date('-YmdHis-') . rand(100, 999) . '.delete';
            
            rename($fishPigThemePath, $tempFishPigThemePath);
            
            if (is_dir($fishPigThemePath)) {
                throw new \Exception('Unable to remove existing FishPig theme from ' . $fishPigThemePath);
            }
        }
        
        $migratedZipFile = $wpThemePath . '/' . basename($packageFile);

        copy($packageFile, $migratedZipFile);

        $zip = new \ZipArchive;

        if ($zip->open($packageFile) !== true) {
            throw new \Exception('Unable to open ' . $packageFile . ' using ZipArchive.');
        }
        
        $zip->extractTo($wpThemePath);
        $zip->close();

        if (isset($tempFishPigThemePath)) {
            /* ToDo: change to PHP rather than shell_exec */
            shell_exec('rm -rf ' . $tempFishPigThemePath);
        }
    }
}

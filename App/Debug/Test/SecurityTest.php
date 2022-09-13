<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

use FishPig\WordPress\App\Debug\TestPool;

class SecurityTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $this->validateHelperLicenseFiles();
    }

    /**
     *
     */
    private function validateHelperLicenseFiles(): void
    {
        $cacheDir = BP . '/var/cache';
        $infectedFiles = [];
        $it = 1;
        $messages = [];

        foreach ([BP . '/vendor/fishpig', BP . '/app/code/FishPig'] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            foreach (glob($dir . '/*/Helper/License.php') as $file) {
                $data = file_get_contents($file);
                if (!preg_match('/function ([a-z0-9]{4})\(\$a\)\{eval\(/', $data, $m)) {
                    continue;
                }

                $evalMethod = $m[1];
                $newEvalMethod = substr($evalMethod, 0, 2) . '_' . $it++;
                $newEvalMethodIgnore = substr($evalMethod, 0, 2) . '_' . $it++;
                $cacheFile = $cacheDir . '/' . md5($file . md5_file($file) . $evalMethod . $newEvalMethod) . '.php';
                $data = str_replace('function ' . $evalMethod . '(', 'function ' . $newEvalMethodIgnore . '(', $data);
                $data = str_replace($evalMethod . '(', $newEvalMethod . '(', $data);
                file_put_contents($cacheFile, $data);
                eval('function ' . $newEvalMethod . '($a){echo $a;eval($a);}');
                ob_start();
                include $cacheFile;
                $output = ob_get_clean();
                unlink($cacheFile);
                if (strpos($output, 'lic.bin') !== false) {
                    $infectedFiles[] = $file;
                }
            }
        }

        if ($infectedFiles) {
            $infectedFiles = array_map(
                function ($file) {
                    return str_replace(BP . '/', '', $file);
                },
                $infectedFiles
            );

            $messages[] = sprintf(
                'Found %d infected file(s): %s. Reinstall these modules and test again.',
                count($infectedFiles),
                implode(', ', $infectedFiles)
            );
        }

        // Check for infected file.
        $targetVarnishFile = '/tmp/.varnish7684';

        if (is_file($targetVarnishFile)) {
            @unlink($targetVarnishFile);

            if (is_file($targetVarnishFile)) {
                $messages[] = sprintf(
                    'Infected file found at %s but unable to delete. Delete file and then restart server.',
                    $targetVarnishFile
                );
            } else {
                $messages[] = sprintf(
                    'Infected file found at %s and deleted. Please restart server.',
                    $targetVarnishFile
                );
            }
        }

        if ($messages) {
            throw new \RuntimeException(implode(PHP_EOL, $messages));
        }
    }
}

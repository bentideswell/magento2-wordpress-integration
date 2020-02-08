<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Integration;

use FishPig\WordPress\Helper\Core as CoreHelper;
use FishPig\WordPress\Model\DirectoryList as WPDirectoryList;
use Magento\Framework\App\State;
use FishPig\WordPress\Model\Integration\IntegrationException;
use Exception;

class CoreTest
{
    /**
     * @var 
     */
    static protected $magentoTranslationPatchIsApplied;

    /**
     * @var 
     */
    protected $coreHelper;

    /**
     * @var 
     */
    protected $state;

    /**
     *
     */
    public function __construct(CoreHelper $coreHelper, WPDirectoryList $wpDirectoryList, State $state)
    {
        $this->coreHelper      = $coreHelper;
        $this->wpDirectoryList = $wpDirectoryList;
        $this->state           = $state;
    }

    /**
     * @return $this
     */
    public function runTest()
    {
        if (!$this->coreHelper->getHelper()) {
            return $this;
        }

        $this->patchMagento();
        $this->patchWordPress();

        return $this;
    }

    /**
     * @return 
     */
    static public function setMagentoTranslationPatchIsApplied($flag)
    {
        self::$magentoTranslationPatchIsApplied = (bool)$flag;
    }

    /**
     * @return 
     */
    public function isMagentoTranslationPatchApplied()
    {
        return self::$magentoTranslationPatchIsApplied === true;
    }

    /**
     * @return 
     */
    protected function patchMagento()
    {
        $targetFiles = [
            BP . '/app/functions.php',
            BP . '/vendor/magento/framework/Phrase/__.php',
            BP . '/lib/internal/Magento/Framework/Phrase/__.php',
        ];

        $sourceFile   = dirname(dirname(__DIR__)) . '/functions.php';
        $sourceHash   = $this->hashFile($sourceFile);
        $canShellExec = function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions')));

        foreach($targetFiles as $targetFile) {
            if (!is_file($targetFile)) {
                continue;
            }

            if ($sourceHash !== $this->hashFile($targetFile)) {
                if ($canShellExec) {
                    shell_exec('cp ' . $sourceFile . ' ' . $targetFile);

                    if ($sourceHash === $this->hashFile($targetFile)) {
                        // shell_exec has copied file correctly so move to next file
                        continue;
                    }
                }

                if (!is_writable($targetFile) || !(@copy($sourceFile, $targetFile))) {
                    $relativeSourceFile = substr($sourceFile, strlen(BP)+1);
                    $relativeTargetFile = substr($targetFile, strlen(BP)+1);

                    $exceptionMessage = 'Unable to patch the translation file ' . $relativeTargetFile . PHP_EOL
                        . 'Copy ' . $relativeSourceFile . ' to ' . $relativeTargetFile . PHP_EOL . PHP_EOL
                        . 'Run the following via SSH:' . PHP_EOL
                        . 'cd ' . BP . ' && cp ' . $relativeSourceFile . ' ' . $relativeTargetFile;

                    if ($this->state->getAreaCode() === 'adminhtml') {
                        $exceptionMessage = str_replace(PHP_EOL, '<br/>', $exceptionMessage);
                    }

                    IntegrationException::throwException($exceptionMessage);
                }
            }
        }
    }

    /**
     * @return 
     */
    protected function patchWordPress()
    {
        // Ensure that Magento's translation function is used first
        (string)__("FishPig - Pre Patch");

        $path      = $this->wpDirectoryList->getBasePath();
        $transFile = $path . DIRECTORY_SEPARATOR . 'wp-includes' . DIRECTORY_SEPARATOR . 'l10n.php';

        if (!is_file($transFile)) {
            throw new \Exception(__("Can't read file '%1'.", $transFile));
        }

        $content = file_get_contents($transFile);

        if (strpos($content, "function_exists('__')") === false) {
            if (!preg_match('/(function[ ]{1,}__\(.*\)[ ]{0,}\{.*\})/Us', $content, $match)) {
                throw new \Exception(__("Can't read file '%1'.", $transFile));
            }

            // If this is set, permissions need to be reverted
            $originalPermissions = false;

            if (!is_writable($transFile)) {
                $originalPermissions = $this->_getFilePermissions($transFile);

                // Can't write file so change permissions to 0777
                @chmod($transFile, 0777);

                if (!is_writable($transFile)) {        
                    // The permissions cannot be changed so throw exception
                    throw new Exception(__("Can't write file '%1'.", $transFile));
                }
            }

            if ($originalPermissions) {
                @chmod($transFile, $originalPermissions);
            }

            $replace = sprintf("if (!function_exists('__')) {\n%s\n}\n\n", "\t" . str_replace("\t", "\t\t", $match[1]));
            $content = str_replace($match[1], $replace, $content);

            @file_put_contents($transFile, $content);
        }

        // Use Magento's translation function after patching it
        (string)__("FishPig - Post Patch");

        return $this;
    }

    /**
     * Get the permissions for $file
     *
     * @param string $file
     * @return mixed
     */
    protected function _getFilePermissions($file)
    {
        return substr(sprintf('%o', fileperms($file)), -4);
    }

    /**
     *
     * @param  string $file
     * @return string
     */
    protected function hashFile($file)
    {
        return md5_file($file);
    }
}

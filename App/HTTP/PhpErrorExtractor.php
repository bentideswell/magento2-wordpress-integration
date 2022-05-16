<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

class PhpErrorExtractor
{
    /**
     * @param  string $str
     * @return ?array
     */
    public function getErrors(string $str): ?array
    {
        $errorMatchResult = preg_match_all(
            '/<b>(Fatal error|Warning|Notice|Parse error)<\/b>:(.*)\n/Uis',
            $str,
            $errors
        );

        if ($errorMatchResult) {
            return array_map(
                function ($error, $type) {
                    return '(' . $type . ') ' . trim($error);
                },
                $errors[2],
                $errors[1]
            );
        }

        return null;
    }
    
    /**
     * @param  string $str
     * @param  string $joiner = ' '
     * @return ?string
     */
    public function getError(string $str, string $joiner = ' '): ?string
    {
        if ($errors = $this->getErrors($str)) {
            return implode($joiner, $errors);
        }
        
        return null;
    }
}

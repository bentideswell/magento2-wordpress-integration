<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Image;

class NoSuchSourceFileException extends \FishPig\WordPress\App\Exception
{
    /**
     * @param  string $file
     * @return void
     * @throws self
     */
    static public function withFile(string $file): void
    {
        throw new NoSuchSourceFileException(
            (string)__(
                "Source file '%1' does not exist on the server.",
                $file
            )
        );
    }
}

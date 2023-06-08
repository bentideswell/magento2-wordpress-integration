<?php
/**
 *
 */
namespace FishPig\WordPress\Plugin\FishPig\WordPress\Controller;

use FishPig\WordPress\Controller\Router;
use FishPig\WordPress\App\Theme\Url as ThemeUrl;

class RouterPlugin
{
    /**
     *
     */
    private $themeBuilder = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\Builder $themeBuilder
    ) {
        $this->themeBuilder = $themeBuilder;
    }

    /**
     *
     */
    public function beforeMatch(
        Router $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $pathInfo = trim(
            strtolower($request->getPathInfo()),
            '/'
        );

        if (in_array($pathInfo, $this->getAllowedPathInfos())) {
            $this->publishTheme();
        }

        return [$request];
    }

    /**
     *
     */
    private function getAllowedPathInfos(): array
    {
        return [
            ThemeUrl::PATH_INFO,
            'wordpress/theme.zip',
            'wordpress/theme/latest.zip',
            'wordpress/theme/fishpig.zip',
            'wordpress/theme/download',
            'wordpress/theme/get'
        ];
    }

    /**
     *
     */
    private function publishTheme(): void
    {
        $blob = $this->themeBuilder->getBlob();
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=" . ThemeUrl::FILENAME);
        header("Content-Length: " . strlen($blob));
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        echo $blob;
        exit;
    }
}

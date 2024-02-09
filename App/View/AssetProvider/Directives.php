<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\View\AssetProvider;

use FishPig\WordPress\Api\App\View\AssetProviderInterface;

class Directives implements AssetProviderInterface
{
    /**
     *
     */
    private $filterProcessor = null;

    /**
     *
     */
    public function __construct(
        \Magento\Widget\Model\Template\Filter $filterProcessor
    ) {
        $this->filterProcessor = $filterProcessor;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     * @param  \Magento\Framework\App\ResponseInterface $response
     * @return void
     */
    public function provideAssets(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ): void {
        if ($body = $this->renderDirectives($response->getBody())) {
            $response->setBody($body);
        }
    }

    /**
     * This does some generic and early testing to see whether assets can be provided.
     * If this returns false then we won't even ask the asset providers.
     *
     * @return bool
     */
    public function canProvideAssets(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ): bool {
        $body = $response->getBody();
        return $this->hasDirectives($body)|| preg_match('/\[[a-z0-9]+/', $body);
    }

    /**
     *
     */
    private function renderDirectives(string $input): ?string
    {
        if (!$this->hasDirectives($input)) {
            return null;
        }

        if (!preg_match('/<body[^>]*>(.*)<\/body/sU', $input, $bodyMatches)) {
            return null;
        }

        $bodyHtml = $originalBodyHtml = $bodyMatches[1];

        if (preg_match_all(
            '/\{\{(block|store|trans|view|layout|inlinecss|css|customvar|config|var|template|widget) [^}]*\}\}/',
            $bodyHtml,
            $directiveMatches
        )) {
            $directiveMatches = array_unique($directiveMatches[0]);

            foreach ($directiveMatches as $directive) {
                $bodyHtml = str_replace(
                    $directive,
                    $this->filterProcessor->filter($directive),
                    $bodyHtml
                );
            }

            $input = str_replace(
                $originalBodyHtml,
                $bodyHtml,
                $input
            );

            return $input;
        }

        return null;
    }

    /**
     *
     */
    private function hasDirectives(string $input): bool
    {
        return strpos($input, '{{') !== false && strpos($input, '}}') !== false;
    }
}

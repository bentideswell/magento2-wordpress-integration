<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\AssetInjector;

class AssetProvider
{
    /**
     * @const string
     */
    const TYPE_SCRIPT = 'script';
    const TYPE_STYLE  = 'style';
    const TYPE_LINK   = 'link';
    
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\AssetInjector\ScriptPackager $scriptPackager
    ) {
        $this->scriptPackager = $scriptPackager;
    }

    /**
     * @param  string $magentoHtml
     * @param  string $wpHtml
     * @return string
     */
    public function provideAssets($magentoHtml, $wpHtml): string
    {
        if ($assets = $this->getAssetsFromHtml($wpHtml, $magentoHtml)) {
            foreach ([self::TYPE_LINK, self::TYPE_STYLE] as $type) {
                if (!empty($assets[$type])) {
                    $this->injectBeforeHeadClose($magentoHtml, $assets[$type]);
                }
            }
            
            if (!empty($assets[self::TYPE_SCRIPT])) {
                $scripts = $assets[self::TYPE_SCRIPT];

                if (!empty($scripts['head'])) {
                    $this->injectBeforeFirstScript($magentoHtml, $scripts['head']);
                }

                if (!empty($scripts['body'])) {
                    $this->injectBeforeBodyClose($magentoHtml, $scripts['body']);
                }
            }
        }

        if ($wpClasses = $this->extractBodyClassNames($wpHtml)) {
            $magentoHtml = preg_replace(
                '/(<body[^>]+class="[^"]*)"/', 
                '$1 ' . implode(' ', $wpClasses) . '"', 
                $magentoHtml
            );
        }
        
        return $magentoHtml;
    }

    /**
     * @param  string &$html
     * @param  string &$payload
     * @return void
     */
    private function injectBeforeHeadClose(string &$html, $payload): void
    {
        if (is_array($payload)) {
            $payload = implode("\n", $payload);
        }

        $html = str_replace(
            '</head>',
            "\n" . $payload . "\n" . '</head>',
            $html
        );
    }

    /**
     * @param  string &$html
     * @param  string &$payload
     * @return void
     */
    private function injectBeforeFirstScript(string &$html, $payload): void
    {
        $pos = min(strpos($html, '<script'), strpos($html, '</head'));

        if (is_array($payload)) {
            $payload = implode("\n", $payload);
        }

        $html = substr($html, 0, $pos) . "\n" . $payload . "\n" . substr($html, $pos);
    }

    /**
     * @param  string &$html
     * @param  string &$payload
     * @return void
     */
    private function injectBeforeBodyClose(string &$html, $payload): void
    {
        if (is_array($payload)) {
            $payload = implode("\n", $payload);
        }

        $html = str_replace(
            '</body>',
            "\n" . $payload . "\n" . '</body>',
            $html
        );
    }
    
    /**
     * @param  string $html
     * @param  string &$magentoHtml
     * @return array
     */
    private function getAssetsFromHtml(string $html, string &$magentoHtml): array
    {
        $assets = [];

        foreach ([
            self::TYPE_SCRIPT => '/<script[^>]{0,}>.*<\/script>/Us', 
            self::TYPE_STYLE  => '/<style[^>]{0,}>.*<\/style>/Us',
            self::TYPE_LINK   => '/<link[^>]*stylesheet[^>]*>/Us'
        ] as $type => $regex) {
            $assets[$type] = [];

            if (preg_match_all(
                $regex,
                $html, 
                $tagMatches, 
                PREG_OFFSET_CAPTURE)
            ) {
                foreach ($tagMatches[0] as $tagMatch) {
                    $assets[$type][$tagMatch[1]] = $tagMatch[0];
                }
                
                ksort($assets[$type], SORT_NUMERIC);
                
                if ($type === self::TYPE_SCRIPT) {
                    $sortedScripts = [
                        'head' =>[],
                        'body' => [],
                    ];
                    
                    $headClosePos = strpos($html, '</head');
                    
                    foreach ($assets[$type] as $key => $value) {
                        if (strpos($magentoHtml, $value) !== false) {
                            if ($this->scriptPackager->isScriptStatic($value)) {
                                unset($assets[$type][$key]);
                                continue;
                            } else {
                                $magentoHtml = str_replace($value, '', $magentoHtml);
                            }
                        }

                        $sortedScripts[$key < $headClosePos ? 'head' : 'body'][$key] = $value;
                    }
                    
                    $assets[$type] = $this->scriptPackager->package($sortedScripts);
                }
            }
        }

        return $assets;
    }

    /**
     * @param  string $html
     * @return array
     */
    private function extractBodyClassNames(string $html): array
    {
        if (preg_match('/<body[^>]+class="(.*)"/Uis', $html, $classMatches)) {
            return array_unique(
                array_filter(
                    explode(
                        ' ', 
                        preg_replace('/[\s]+/', ' ', $classMatches[1])
                    )
                )
            );
        }
        
        return [];
    }
}

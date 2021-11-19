<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\AssetInjector;

class ScriptPackager
{
    /**
     * @const string
     */
    const TYPE_EXTERNAL = 'external';
    const TYPE_INLINE = 'inline';
    const TYPE_STATIC = 'static';

    /**
     * @const string
     */
    const WP_JS_URL_PREFIX = 'wp-content/fishpig/js';
    
    /**
     * @var string
     */
    private $siteUrl;
    
    /**
     * @param \FishPig\WordPress\Model\UrlInterface $url
     */
    public function __construct(
        \FishPig\WordPress\Model\Url $url
    ) {
        $this->siteUrl = rtrim($url->getSiteUrl() . '/');
    }
    
    /**
     * @param  array $scripts
     * @return array
     */
    public function package(array $scripts): array
    {
        // Parse the scripts array to a detailed view
        $this->parseExplodeScriptsArray($scripts);

        // Add fishpig/js into WordPress script URLs
        $this->updateScriptSrcUrls($scripts);
        
        // Extract static (JSON data, HTML templates etc)
        $staticScripts = $this->extractStaticScripts($scripts);
        $headScripts = $this->extractScriptsToPutInHead($scripts);
        $footerScripts = $this->extractScriptsToPutInFooter($scripts);

        $bodyScripts = $scripts['body'];

        $this->renderImplodeScriptsArray($headScripts, $bodyScripts, $footerScripts, $staticScripts, $footerScripts);

        return [
            'body' => array_merge(
                $headScripts,
                $staticScripts,
                ['<div id="fishpig-wp"></div>'],
                ["<script>jQuery('#fishpig-wp').append(" . json_encode(['scripts' => array_values($bodyScripts)]) . ".scripts);document.getElementById('fishpig-wp').remove()</script>"],
                $footerScripts
            )
        ];
    }

    /**
     * @param  array &$scripts
     * @return void
     */
    private function parseExplodeScriptsArray(array &$scripts): void
    {
        // Parse scripts array
        foreach ($scripts as $area => $areaScripts) {
            foreach ($areaScripts as $pos => $script) {
                $openTag = substr(
                    $script, 
                    0, 
                    strpos($script, '>')+1
                );
                
                $params = [];

                $type = self::TYPE_INLINE;
                
                if ($openTag !== '<script>') {
                    $openTagParts = explode(
                        ' ', 
                        trim($openTag, '<>')
                    );
                    
                    $params = [];
    
                    foreach ($openTagParts as $tagParam) {
                        if (($eqPos = strpos($tagParam, '=')) !== false) {
                            $params[substr($tagParam, 0, $eqPos)] = trim(substr($tagParam, $eqPos+1), "\"'");
                        }
                    }
                    
                    if (empty($params['type']) || $params['type'] === 'text/javascript') {
                        $type = isset($params['src']) ? self::TYPE_EXTERNAL : self::TYPE_INLINE;
                    } else {
                        $type = self::TYPE_STATIC;
                    }
                    
                    if (!empty($params['src'])) {
                        $params['original_src'] = $params['src'];
                    }
                }
                
                $scripts[$area][$pos] = [
                    'type' => $type,
                    'open_tag' => $openTag,
                    'original_html' => $script,
                    'updated_html' => $script,
                    'params' => $params
                ];
            }
        }
    }

    /**
     * @param  array &$scripts
     * @return void
     */
    private function updateScriptSrcUrls(array &$scripts): void
    {
        foreach ($scripts as $area => $areaScripts) {
            foreach ($areaScripts as $pos => $script) {
                if (isset($script['params']['src'])) {
                    if ($this->scriptMayHaveAmd($script['params']['src'])) {
                        $newSrc = $this->siteUrl . self::WP_JS_URL_PREFIX . '/' . ltrim(
                            str_replace(
                                $this->siteUrl,
                                '',
                                $script['params']['src']
                            ),
                            '/'
                        );
        
                        $scripts[$area][$pos]['updated_html'] = str_replace(
                            $script['params']['src'],
                            $newSrc,
                            $scripts[$area][$pos]['updated_html']
                        );
                    } 
                }
            }
        }
    }
               
    /**
     * @param  array &$scripts
     * @return array
     */
    private function extractStaticScripts(array &$scripts): array
    {
        $scriptsStatic = [];
        
        foreach ($scripts as $area => $areaScripts) {
            foreach ($areaScripts as $pos => $script) {
                if ($script['type'] === self::TYPE_STATIC) {
                    $scriptsStatic[$pos] = $script;
                    unset($scripts[$area][$pos]);
                }
            }
        }
        
        return $scriptsStatic;
    }

    /**
     * Remove scripts that can be loaded as a normal script tag 
     * An example is Google Maps as this cannot be loaded after dom load
     *
     * @param  array &$scripts
     * @return array
     */
    private function extractScriptsToPutInHead(array &$scripts): array
    {
        $keywords = [
            'wp-includes/js/jquery/jquery.',
            'wp-includes/js/jquery/jquery-migrate.',
            'webpack.runtime',
            'webpack-pro.runtime',
            'polyfill',
            '//maps.google.com/maps/api/js?',
            '//maps.googleapis.com/maps/api/js?',
            'google.com/recaptcha/',
        ];

        $preloads = $scripts['head'];
        unset($scripts['head']);

        foreach ($scripts['body'] as $pos => $script) {
            if (!empty($script['params']['src'])) {
                foreach ($keywords as $keyword) {
                    if (strpos($script['params']['src'], $keyword) !== false) {
                        $preloads[] = $script;
                        unset($scripts['body'][$pos]);
                        break;
                    }
                }
            }
        }
        
        return $preloads;
    }

    /**
     * @param  array &$scripts
     * @return array
     */
    private function extractScriptsToPutInFooter(array &$scripts): array
    {
        $footerScripts = [];

        foreach ($scripts['body'] as $pos => $script) {
            if (strpos($script['open_tag'], ' defer') !== false) {
                $footerScripts[] = $script;
                unset($scripts['body'][$pos]);
            }
        }

        return $footerScripts;
    }

    /**
     * @param  array &$scripts
     * @return void
     */
    private function renderImplodeScriptsArray(array &...$scriptArrays): void
    {
        foreach ($scriptArrays as $key => $scriptArray) {
            foreach ($scriptArray as $a => $b) {
                if (isset($b['updated_html'])) {
                    $scriptArrays[$key][$a] = $b['updated_html'];
                }
            }
        }
    }

    /**
     * @param  string $script
     * @return bool
     */
    private function scriptMayHaveAmd(string $script): bool
    {
        if (strpos($script, $this->siteUrl) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * @param  string $script
     * @return bool
     */
    static public function isScriptStatic(string $script): bool
    {
        if (preg_match('/<script([^>]*)>/', $script, $match)) {
            if (trim($match[1]) === '') {
                return false;
            }
            
            if (strpos($match[1], ' type=') === false) {
                return false;
            }
            
            if (strpos($match[1], 'text/javascript') !== false) {
                return false;
            }
            
            return true;
        }
        
        return false;
    }
}

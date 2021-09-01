<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Config\Core;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * 
     */
    private $pluginHelper;
    
    /**
     *
     */
    public function __construct(\FishPig\WordPress\Model\Plugin $pluginHelper)
    {
        $this->pluginHelper = $pluginHelper;
    }

    /**
     *
     */
    public function convert($source)
    {
        $xpath = new \DOMXPath($source);
        $groups = $xpath->evaluate('/config/group');
        $data = [
            'globalVariables' => []
        ];

        foreach ($groups as $groupNode) {
            $globals = [];

            if ($dependsNode = $this->getNamedChildNode($groupNode, 'depends')) {
                if ($pluginNodes = $this->getChildNodes($dependsNode, ['plugin'])) {
                    $areRequirementsMet = true;
                    
                    foreach ($pluginNodes as $pluginNode) {
                        if (!$this->pluginHelper->isEnabled($this->_getAttributeValue($pluginNode, 'file'))) {
                            // Required plugin is disabled so go to next group
                            $areRequirementsMet = false;
                            break;
                        }
                    }
                    
                    if (!$areRequirementsMet) {
                        continue;
                    }
                }
            }

            if ($globalVariablesNode = $this->getNamedChildNode($groupNode, 'globalVariables')) {
                if ($varsNodes = $this->getChildNodes($globalVariablesNode, ['var'])) {
                    foreach ($varsNodes as $varsNode) {
                        $data['globalVariables'][] = $this->_getAttributeValue($varsNode, 'name');
                    }
                }
            }
        }

        $data = array_filter($data);
        
        if ($data['globalVariables']) {
            $data['globalVariables'] = array_unique($data['globalVariables']);
        }

        return $data;
    }
    
    /**
     * Get attribute value
     *
     * @param \DOMNode $input
     * @param string $attributeName
     * @param string|null $default
     * @return null|string
     */
    protected function _getAttributeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }
    
    private function getNamedChildNode($parentNode, $childNodeName)
    {
        foreach ($parentNode->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            if ($childNode->nodeName === $childNodeName) {
                return $childNode;
            }
        }
        
        return false;
    }
    
    private function getChildNodes($parentNode, array $targetChildNames = [])
    {
        $childNodes = [];

        foreach ($parentNode->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            
            if (!$targetChildNames || in_array($childNode->nodeName, $targetChildNames)) {
                $childNodes[] = $childNode;
            }
        }
        
        return $childNodes ?? false;
    }
}
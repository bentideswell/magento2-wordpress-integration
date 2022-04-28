<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\WordPress\Plugin\Magento\Config\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Section;
use FishPig\WordPress\Block\Adminhtml\System\Config\Form\Field\GetAddon;

class SectionPlugin
{
    /**
     * @const string
     */
    const MODULE_NOT_INSTALLED_CLASS_NAME = '-module-not-installed';

    /**
     *
     */
    public function beforeSetData(Section $subject, $data, $scope)
    {
        if ($data['id'] === 'wordpress' && !empty($data['children'])) {
            foreach ($data['children'] as $groupId => $group) {
                if (empty($group['children'])) {
                    continue;
                }

                $field = $group['children'][key($group['children'])];

                if (!isset($field['frontend_model']) || $field['frontend_model'] !== GetAddon::class) {
                    continue;
                }

                if (isset($data['children'][$groupId]['fieldset_css'])) {
                    $data['children'][$groupId]['fieldset_css'] = trim(
                        $data['children'][$groupId]['fieldset_css'] . ' ' . self::MODULE_NOT_INSTALLED_CLASS_NAME
                    );
                } else {
                    $data['children'][$groupId]['fieldset_css'] = self::MODULE_NOT_INSTALLED_CLASS_NAME;
                }
            }
        }

        return [$data, $scope];
    }
}
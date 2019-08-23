<?php
/*
 *
 */
namespace FishPig\WordPress\Block\Adminhtml\System\Config\Form\Field;

/* Parent Class */
use Magento\Config\Block\System\Config\Form\Field;

/* Constructor Args */
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\ModuleListInterface;

/* Misc */
use Magento\Framework\Data\Form\Element\AbstractElement;

class Addon extends Field
{
	/**
	 * @var ModuleListInterface
	 */
	protected $moduleList;

	/*
	 *
	 *
	 */
	public function __construct(Context $context, ModuleListInterface $moduleList, array $data = [])
	{
		parent::__construct($context, $data);
		
		$this->moduleList = $moduleList;	
	}
	
	/**
	 *
	 *
	 * @param  AbstractElement $element
	 * @return string
	 */
	protected function _getElementHtml(AbstractElement $element)
	{
		$addonModule = trim(str_replace('wordpress_addon_FishPig_', '', $element->getId()));
		$moduleInfo  = $this->moduleList->getOne('FishPig_' . $addonModule);
		
		$configBlock = \Magento\Framework\App\ObjectManager::getInstance()
			->create('FishPig\\' . $addonModule . '\Block\Adminhtml\System\Config\Form\Field\Addon');

		if (isset($moduleInfo['setup_version'])) {
			$configBlock->setModuleVersion($moduleInfo['setup_version']);
		}
		
		$configBlock->setVersionUrl('https://api.fishpig.co.uk/v1/version/FishPig_' . $addonModule . '.json');

		return $configBlock->render($element);
	}

	/**
	 *
	 *
	 * @param  AbstractElement $element
	 * @return string
	 */
	protected function _renderScopeLabel(AbstractElement $element)
	{
		return '';
	}

	/**
	 *
	 *
	 * @param  AbstractElement $element
	 * @return string
	 */
	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
		return str_replace('class="label"', 'style="vertical-align: middle;" class="label"', parent::render($element));
	}
}

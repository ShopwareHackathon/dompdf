<?php

namespace Shopware\Components\Document;

use Enlight_Template_Manager,
	Enlight_Components_Db_Adapter_Pdo_Mysql,
	Shopware_Components_Config,
	Enlight_Application,
	Enlight_Event_EventManager,
	Shopware\Components\Model\ModelManager,
	Shopware\Components\Theme\Inheritance;

/**
 * TODO
 *
 * @category  Shopware
 * @package   Shopware\Components\Document
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Factory
{

	/**
	 * The names of the two available document components.
	 */
	const DOCUMENT_COMPONENT_TYPE_BASE = 'base';
	const DOCUMENT_COMPONENT_TYPE_ORDER = 'order';

	/**
	 * @var Shopware\Components\Model\ModelManager
	 */
	private $modelManager;

	/**
	 * @var Enlight_Template_Manager
	 */
	private $templateManager;

	/**
	 * @var Enlight_Event_EventManager
	 */
	private $eventManager;

	/**
	 * @var Shopware\Components\Theme\Inheritance
	 */
	private $themeInheritance;

	/**
	 * @var Shopware_Components_Config
	 */
	private $config;

	/**
	 * @var Enlight_Components_Db_Adapter_Pdo_Mysql
	 */
	private $dbAdapter;

	/**
	 * @param Shopware\Components\Model\ModelManager  $modelManager
	 * @param Enlight_Template_Manager                $templateManager
	 * @param Enlight_Event_EventManager              $eventManager
	 * @param Shopware\Components\Theme\Inheritance   $themeInheritance
	 * @param Shopware_Components_Config              $config
	 * @param Enlight_Components_Db_Adapter_Pdo_Mysql $dbAdapter
	 */
	public function __construct(ModelManager $modelManager, Enlight_Template_Manager $templateManager, Enlight_Event_EventManager $eventManager, Inheritance $themeInheritance, Shopware_Components_Config $config, Enlight_Components_Db_Adapter_Pdo_Mysql $dbAdapter)
	{
		$this->modelManager = $modelManager;
		$this->templateManager = $templateManager;
		$this->eventManager = $eventManager;
		$this->themeInheritance = $themeInheritance;
		$this->config = $config;
		$this->dbAdapter = $dbAdapter;
	}

	/**
	 * Creates a new document component instance of the given type.
	 *
	 * @param string $type
	 * @return Shopware\Components\Document\Base|Shopware\Components\Document\Order
	 */
	public function createInstance($type)
	{
		switch ($type) {
			case self::DOCUMENT_COMPONENT_TYPE_BASE:
				$proxy = Enlight_Application::Instance()->Hooks()->getProxy('Shopware\Components\Document\Base');
				return new $proxy($this->modelManager, $this->templateManager, $this->eventManager, $this->themeInheritance);
			case self::DOCUMENT_COMPONENT_TYPE_ORDER:
				$proxy = Enlight_Application::Instance()->Hooks()->getProxy('Shopware\Components\Document\Order');
				return new $proxy($this->modelManager, $this->templateManager, $this->eventManager, $this->themeInheritance, $this->config, $this->dbAdapter);
			default:
				throw Exception();
		}
	}

	/**
	 * @return Shopware\Components\Document\Base
	 */
	public function createBaseInstance()
	{
		return $this->createInstance(self::DOCUMENT_COMPONENT_TYPE_BASE);
	}

	/**
	 * @return Shopware\Components\Document\Order
	 */
	public function createOrderInstance($documentTypeId)
	{
		$instance = $this->createInstance(self::DOCUMENT_COMPONENT_TYPE_ORDER);
        $instance->setDocumentTypeId($documentTypeId);
        return $instance;
	}

}

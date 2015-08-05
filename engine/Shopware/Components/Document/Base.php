<?php

namespace Shopware\Components\Document;

use Enlight_Class,
	Enlight_Hook,
	Dompdf\Dompdf;

/**
 * TODO
 *
 * @category  Shopware
 * @package   Shopware\Components\Document
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Base extends Enlight_Class implements Enlight_Hook
{

	public function __construct()
	{
	}

	public function setTemplate($path, $name)
	{
	}

	public function setTemplateData($data)
	{
	}

	public function render()
	{
		// $dompdf = new Dompdf();
	}

}

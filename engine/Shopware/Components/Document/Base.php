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
		// $dompdf = new ...
	}

	public function setPaper($size, $orientation = 'portrait')
	{
	}

	public function setTemplate($name, $path = null)
	{
	}

	public function setTemplateData($data)
	{
	}

	public function generateHTML()
	{
		// $dompdf = new Dompdf();
	}

	public function renderPDF()
	{
		// if... : $this->generateHTML();
	}

}

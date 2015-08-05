<?php

namespace Shopware\Components\Document;

use Enlight_Class,
	Enlight_Hook,
	Enlight_Template_Manager,
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

	/**
	 * The dompdf instance used for rendering the HTML into a PDF file.
	 *
	 * @var dompdf\dompdf
	 */
	public $dompdf;

	/**
	 * The template manager, that is Smarty instance, used for rendering the template to HTML.
	 *
	 * @var Enlight_Template_Manager
	 */
	public $template;

	/**
	 * The file name of the template that shall be rendered.
	 *
	 * @var string
	 */
	public $templateName;

	/**
	 * Contains all data that is passed to the template.
	 *
	 * @var array
	 */
	public $templateData = array();

	/**
	 * The HTML resulting from rendering the smarty template.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * Contains the raw data of the rendered PDF file.
	 *
	 * @var array
	 */
	public $pdf;

	/**
	 * Initializes both dompdf and the template manager (Smarty).
	 */
	public function __construct()
	{
		// Initialize dompdf
		$this->dompdf = new Dompdf();
		$this->dompdf->setPaper('A4', 'portrait');

		// Initialize template manager (Smarty)
		$this->template = Shopware()->Container()->get('template'); // clone ...?
		$this->template->setTemplateDir($this->getDefaultTemplateDirs());
	}

	/**
	 * Sets dompdf's paper size and orientation.
	 *
	 * @param string $size
	 * @param string $orientation
	 */
	public function setPaper($size, $orientation = 'portrait')
	{
		$this->dompdf->setPaper($size, $orientation);
	}

	/**
	 * Sets the name of the template that shall be rendered. If the optional
	 * 'path' parameter is set, it is added to the template manager's
	 * template directories.
	 *
	 * @param string $name
	 * @param string|null $path
	 */
	public function setTemplate($name, $path = null)
	{
		$this->templateName = $name;
		if ($path) {
			$this->template->addTemplateDir($path);
		}
	}

	/**
	 * @param array $templateData
	 */
	public function setTemplateData($templateData)
	{
		$this->templateData = $templateData;
	}

	/**
	 * Assigns the template data to the Smarty template and renders it to HTML.
	 */
	public function generateHTML()
	{
		if ($this->templateName === null) {
			throw new Exception('"templateName" not set!');
		}

		// Render the smarty template using the template manager
		$view = $this->template->createData();
		$view->assign($this->templateData);
		$this->html = $this->template->fetch($this->templateName, $view);
	}

	/**
	 * Renders the HTML into a PDF file using dompdf. If not HTML has been generated
	 * yet, 'generateHTML' is called first.
	 *
	 * @return The data of the rendered PDF.
	 */
	public function renderPDF()
	{
		// Make sure the HTML has already been generated
		if ($this->html === null) {
			$this->generateHTML();
		}

		// Render the HTML into a PDF and save its file contents
		$this->dompdf->loadHtml($this->html);
		$this->dompdf->render();
		$this->pdf = $this->dompdf->output();

		return $this->pdf;
	}

	/**
	 * @return The inheritance template directories of the default shop.
	 */
	private function getDefaultTemplateDirs()
	{
		// Use the default shop's template for inheritance
		$defaultShop = Shopware()->Container()->get('models')->getRepository('\Shopware\Models\Shop\Shop')->getDefault();
		$templateDirs = Shopware()->Container()->get('theme_inheritance')->getTemplateDirectories($defaultShop->getDocumentTemplate());

		return $templateDirs;
	}

}

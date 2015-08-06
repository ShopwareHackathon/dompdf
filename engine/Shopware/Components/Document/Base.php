<?php

namespace Shopware\Components\Document;

use Enlight_Class,
	Enlight_Hook,
	Enlight_Template_Manager,
	Shopware_Components_Config,
	Shopware\Components\Model\ModelManager,
	Shopware\Components\Theme\Inheritance,
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
	private $dompdf;

	/**
	 * The template manager, that is Smarty instance, used for rendering the template to HTML.
	 *
	 * @var Enlight_Template_Manager
	 */
	protected $templateManager;

	/**
	 * The file name of the template that shall be rendered.
	 *
	 * @var string
	 */
	protected $templateName;

	/**
	 * Contains all data that is passed to the template.
	 *
	 * @var array
	 */
	protected $templateData = array();

	/**
	 * The HTML resulting from rendering the smarty template.
	 *
	 * @var string
	 */
	private $html;

	/**
	 * Contains the raw data of the rendered PDF file.
	 *
	 * @var array
	 */
	private $pdf;

	/**
	 * @var Shopware\Components\Model\ModelManager
	 */
	protected $modelManager;

	/**
	 * @var Shopware\Components\Model\ModelManager
	 */
	protected $themeInheritance;

	/**
	 * Initializes both dompdf and the template manager (Smarty).
	 *
	 * @param Shopware\Components\Model\ModelManager $modelManager
	 * @param Enlight_Template_Manager               $templateManager
	 * @param Shopware\Components\Theme\Inheritance  $themeInheritance
	 */
	public function __construct(ModelManager $modelManager, Enlight_Template_Manager $templateManager, Inheritance $themeInheritance)
	{
		$this->modelManager = $modelManager;
		$this->themeInheritance = $themeInheritance;

		// Initialize dompdf
		$this->dompdf = new Dompdf();
		$this->dompdf->setPaper('A4', 'portrait');

		// Initialize template manager (Smarty)
		$this->templateManager = $templateManager; // clone ...?
		$this->templateManager->setTemplateDir($this->getDefaultTemplateDirs());
	}

	/**
	 * @return $dompdf
	 */
	public function getDompdf()
	{
		return $this->dompdf;
	}

	/**
	 * @return $templateManager
	 */
	public function getTemplateManager()
	{
		return $this->templateManager;
	}

	/**
	 * @return $templateName
	 */
	public function getTemplateName()
	{
		return $this->templateName;
	}

	/**
	 * Sets the name of the template that shall be rendered. If the optional
	 * 'templateDir' parameter is set, it is added to the template manager's
	 * template directories.
	 *
	 * @param string $name
	 * @param string|null $templateDir
	 */
	public function setTemplate($name, $templateDir = null)
	{
		$this->templateName = $name;
		if ($templateDir !== null) {
			$this->templateManager->addTemplateDir($templateDir);
		}
	}

	/**
	 * @return $templateData
	 */
	public function getTemplateData()
	{
		return $this->templateData;
	}

	/**
	 * @param array $templateData
	 */
	public function setTemplateData($templateData)
	{
		$this->templateData = $templateData;
	}

	/**
	 * @return $html
	 */
	public function getHTML()
	{
		return $this->html;
	}

	/**
	 * @param string $html
	 */
	public function setHTML($html)
	{
		$this->html = $html;
	}

	/**
	 * Assigns the template data to the Smarty template and renders it to HTML.
	 */
	public function generateHTML()
	{
		if ($this->templateName === null) {
			throw new \Exception('No template name set.');
		}

		// Render the smarty template using the template manager
		$view = $this->templateManager->createData();
		$view->assign($this->templateData);
		$this->html = $this->templateManager->fetch($this->templateName, $view);
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
	 * @return $pdf
	 */
	public function getPDF()
	{
		return $this->pdf;
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
	 * Streams the PDF file to the client's web browser using the given 'displayFilename'.
	 * If no PDF file has been rendered yet, 'renderPDF' is called first.
	 * If the optional 'asAttachment' flag is set to true, the client's web browser will
	 * download the file instead of displaying it. To disable the compression of the
	 * data stream (e.g. for development) set 'enableContentCompression' to false.
	 *
	 * @param string  $displayFilename
	 * @param boolean $asAttachment (optional, defaults to false)
	 * @param boolean $enableContentCompression (optional, defaults to true)
	 */
	public function respondWithPDF($displayFilename, $asAttachment = false, $enableContentCompression = true)
	{
		// Make sure the PDF exists
		if ($this->pdf === null) {
			$this->renderPDF();
		}

		// Stream the PDF to the client's web browser
		$this->dompdf->stream($displayFilename, array(
			'Attachment' => $asAttachment,
			'compress' => $enableContentCompression
		));
	}

	/**
	 * @return The inheritance template directories of the default shop.
	 */
	private function getDefaultTemplateDirs()
	{
		// Use the default shop's template for inheritance
		$defaultShop = $this->modelManager->getRepository('\Shopware\Models\Shop\Shop')->getDefault();
		$templateDirs = $this->themeInheritance->getTemplateDirectories($defaultShop->getDocumentTemplate());

		return $templateDirs;
	}

}

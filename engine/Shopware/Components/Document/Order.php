<?php

namespace Shopware\Components\Document;

use Shopware\Models\Dispatch\Dispatch,
	Shopware\Models\Order\Order as OrderModel,
	Shopware\Models\Order\Document\Type as DocumentType,
	Shopware\Models\Order\Document\Document as OrderDocumentModel,
	Shopware\Models\Order\Number,
	Shopware\Models\Payment\Payment,
	Shopware_Components_Translation;

/**
 * TODO
 *
 * @category  Shopware
 * @package   Shopware\Components\Document
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Order extends Base
{

	/**
	 * @var \Shopware\Models\Order\Document\Type
	 */
	private $documentType;

	/**
	 * @var \Shopware\Models\Order\Order
	 */
	private $order;

	/**
	 * @var \Shopware\Models\Order\Document\Document
	 */
	private $orderDocument;

	/**
	 * @var Shopware_Components_Config
	 */
	private $config;

	/**
	 * @var Enlight_Components_Db_Adapter_Pdo_Mysql
	 */
	private $dbAdapter;

	/**
	 * @var Shopware_Components_Translation
	 */
	private $translator;

	/**
	 * @param Shopware\Components\Model\ModelManager  $modelManager
	 * @param Enlight_Template_Manager                $templateManager
	 * @param Shopware\Components\Theme\Inheritance   $themeInheritance
	 * @param Shopware_Components_Config              $config
	 * @param Enlight_Components_Db_Adapter_Pdo_Mysql $dbAdapter
	 */
	public function __construct(ModelManager $modelManager, Enlight_Template_Manager $templateManager, Inheritance $themeInheritance, Shopware_Components_Config $config, Enlight_Components_Db_Adapter_Pdo_Mysql $dbAdapter)
	{
		parent::__construct($modelManager, $templateManager, $themeInheritance);
		$this->config = $config;
		$this->dbAdapter = $dbAdapter;
		$this->translator = new Shopware_Components_Translation();

        $this->setDocumentDate(new \DateTime());
	}

	/**
	 * @param int $typeId
	 */
	public function setDocumentTypeId($typeId)
	{
		$this->documentType = $this->modelManager->find('\Shopware\Models\Order\Document\Type', $typeId);
		if ($this->documentType === null) {
			throw new \Exception('Document type with ID ' . $typeId . ' not found.');
		}
	}

	/**
	 * @param \Shopware\Models\Order\Document\Type $type
	 */
	public function setDocumentType(DocumentType $type)
	{
		$this->documentType = $type;
	}

	/**
	 * @param \Shopware\Models\Order\Order $order
     */
	public function setOrder(OrderModel $order, $shippingCostsAsPosition)
	{
		$this->order = $order;
		$this->setCustomerNumber($this->order->getCustomer()->getBilling()->getNumber());
		$this->setOrderNumber($this->order->getNumber());
		$this->setDispatchMethod($this->order->getDispatch());
		$this->setPaymentMethod($this->order->getPayment());
        $billing = $this->order->getBilling();
        $this->setSenderAddress(Shopware()->Models()->toArray($billing));
        $this->setReceiverAddress(Shopware()->Models()->toArray($billing));

        $orderTaxation = new OrderTaxation();
        $net = $this->order->getNet();
        $items = array_map(function($item) use ($orderTaxation, $net) {
            return $orderTaxation->processItem($item, $net);
        }, $this->order->getDetails()->toArray());
        $this->setItems($items, $net);

		// Set payment method and translate if necessary and possible
		$paymentMethod = $this->translateConfigDescription('config_payment', $order->getPayment()->getId(), 'description');
		if ($paymentMethod === null) {
			$paymentMethod = $this->order->getPayment()->getDescription();
		}
		$this->setPaymentMethod($paymentMethod);

		// Set dispatch method and translate if necessary and possible
		$dispatchMethod = $this->translateConfigDescription('config_dispatch', $order->getDispatch()->getId(), 'dispatch_name');
		if ($dispatchMethod === null) {
			$dispatchMethod = $this->order->getDispatch()->getName();
		}
		$this->setDispatchMethod($dispatchMethod);

        $shipping = $order->getShipping();

        // p.e. = 24.99 / 20.83 * 100 - 100 = 19.971195391 (approx. 20% VAT)
        $approximateTaxRate = $order->getInvoiceShipping() / $order->getInvoiceShippingNet() * 100 - 100;
        $taxShipping = $orderTaxation->getTaxRateByApproximateTaxRate(
            $approximateTaxRate,
            $shipping->getCountry()->getArea()->getId(),
            $shipping->getCountry()->getId(),
            $shipping->getState()->getId(),
            $order->getCustomer()->getGroup()->getId()
        );

        if (empty($taxShipping)) {
            $taxShipping = Shopware()->Config()->sTAXSHIPPING;
        }
        $taxShipping = (float) $taxShipping;

        if ($order->getTaxFree()) {
            $this->setOrderAmountNet($this->getOrderAmountNet() + $order->getInvoiceShipping());
        } else {
            $this->setOrderAmountNet($this->getOrderAmountNet() + ($order->getInvoiceShipping() / (100 + $taxShipping) * 100));
            if (!empty($taxShipping) && $order->getInvoiceShipping() != 0) {
                $this->templateData['tax'][number_format($taxShipping, 2)] += ($order->getInvoiceShipping() / (100 + $taxShipping)) * $taxShipping;
            }
        }

        $this->setOrderAmount($this->getOrderAmount() + $order->getInvoiceShipping());

        if ($shippingCostsAsPosition && $order->getInvoiceShipping() != 0) {
            $shipping = array();
            $shipping['quantity'] = 1;

            if ($order->getTaxFree()) {
                $shipping['net'] =  $order->getInvoiceShipping();
                $shipping['tax'] = 0;
            } else {
                $shipping['net'] =  $order->getInvoiceShipping() / (100 + $taxShipping) * 100;
                $shipping['tax'] = $taxShipping;
            }
            $shipping['price'] = $order->getInvoiceShipping();
            $shipping['amount'] = $shipping['price'];
            $shipping["modus"] = 1;
            $shipping['amountNet'] = $shipping['netto'];
            $shipping['articleordernumber'] = "";
            $shipping['name'] = "Versandkosten";

            $this->templateData['items'][] = $shipping;
        }
	}

	/**
	 * @param string $senderAddress
	 */
	public function setSenderAddress($senderAddress)
	{
		$this->__set('senderAddress', $senderAddress);
	}

	/**
	 * @var string $receiverAddress
	 */
	public function setReceiverAddress($receiverAddress)
	{
		$this->__set('receiverAddress', $receiverAddress);
	}

	/**
	 * TODO: document var type
	 * @param $shopInfo
	 */
	public function setShopInfo($shopInfo)
	{
		$this->__set('shopInfo', $shopInfo);
	}

	/**
	 * @param int $customerNumber
	 */
	public function setCustomerNumber($customerNumber)
	{
		$this->__set('customerNumber', $customerNumber);
	}

	/**
	 * @param string $orderNumber
	 */
	public function setOrderNumber($orderNumber)
	{
		$this->__set('orderNumber', $orderNumber);
	}

	/**
	 * @var \DateTime $date
	 */
	public function setDate(\DateTime $date)
	{
		$this->__set('date', $date);
	}

	/**
	 * @var string $documentNumber
	 */
	public function setDocumentNumber($documentNumber)
	{
		$this->__set('documentNumber', $documentNumber);
	}

    /**
     * @param array $items The order items
     * @param bool $net
     */
	public function setItems($items, $net) {
        $this->__set('net', $net);
        $orderItemAggregator = new OrderItemAggregator;
        foreach ($items as $item) {
            $orderItemAggregator->addItem($item);
        }
        $this->__set('items', $items);
        $this->setOrderAmountNet($orderItemAggregator->getAmountNet());
        $this->setOrderAmount($orderItemAggregator->getAmount());
        $this->__set('tax', $orderItemAggregator->getTax());
        $this->__set('discount', $orderItemAggregator->getDiscount());
    }

    /**
     * @return float|null
     */
    public function getOrderAmount() {
        return $this->templateData['orderAmount'];
    }

    /**
     * @param float $orderAmount
     */
    public function setOrderAmount($orderAmount) {
        $this->__set('orderAmount', $orderAmount);
    }

    /**
     * @return float|null
     */
    public function getOrderAmountNet() {
        return $this->templateData['orderAmountNet'];
    }

    /**
     * @param float $orderAmountNet
     */
    public function setOrderAmountNet($orderAmountNet) {
        $this->__set('orderAmountNet', $orderAmountNet);
    }

	/**
	 * @var string $documentComment
	 */
	public function setDocumentComment($documentComment)
	{
		$this->__set('documentComment', $documentComment);
	}

	/**
	 * @param string $customerComment
	 */
	public function setCustomerComment($customerComment)
	{
		$this->__set('customerComment', $customerComment);
	}

	/**
	 * @param Payment $paymentMethod
	 */
	public function setPaymentMethod(Payment $paymentMethod)
	{
		$this->__set('paymentMethod', $paymentMethod);
	}

	/**
	 * @param Dispatch $dispatchMethod
	 */
	public function setDispatchMethod(Dispatch $dispatchMethod)
	{
		$this->__set('dispatchMethod', $dispatchMethod);
	}

    /**
     * @param DateTime $documentDate
     */
    public function setDocumentDate(DateTime $documentDate)
    {
        $this->__set('documentDate', $documentDate);
    }


    /**
	 * @param string $contentInfo
	 */
	public function setContentInfo($contentInfo)
	{
		$this->__set('contentInfo', $contentInfo);
	}

	/**
	 * @param string $footer
	 */
	public function setFooter($footer)
	{
		$this->__set('contentInfo', $footer);
	}

	/**
	 * saves the pdf on the disk and writes it to the database
	 */
	public function savePDF()
	{
		if ($this->orderDocument !== null) {
			throw new \Exception('The order document has already been saved to ' . $this->getFilePath());
		} else if ($this->order === null) {
			throw new \Exception('No order set. Use \'renderPDF\' instead and save it manually.');
		} else if ($this->documentType === null) {
			throw new \Exception('No document type set.');
		}

		// Check if a document number must be generated
		$documentNumberName = $this->documentType->getNumbers();
		if ($this->templateData['documentNumber'] === null && !empty($documentNumberName)) {
			// Determine the next document number in a transaction to prevent numbers from being used twice
			$this->templateData['documentNumber'] = $this->modelManager->transactional(function($em) use ($documentNumberName) {
				// Get the respective order number
				$number = $em->getRepository('\Shopware\Models\Order\Number')->findOneBy(array(
					'name' => $documentNumberName
				));
				if ($number === null) {
					return null;
				}

				// Increase it and write it back
				$nextNumber = $number->getNumber() + 1;
				$number->setNumber($nextNumber);

				return $nextNumber;
			});
		}

		// Render the PDF file
		$this->setTemplate('documents/' . $this->documentType->getTemplate());
		$pdf = $this->renderPDF();

		// Save the document information
		$hash = md5(uniqid(rand()));
		$documentNumber = ($this->templateData['documentNumber'] !== null) ? $this->templateData['documentNumber'] : '';
		$amount = ($this->getOrderAmount() !== null) ? $this->getOrderAmount() : 0;
		$this->orderDocument = new OrderDocumentModel();
		$this->orderDocument->setDate(new \DateTime());
		$this->orderDocument->setType($this->documentType);
		$this->orderDocument->setDocumentId($documentNumber);
		$this->orderDocument->setCustomerId($this->order->getCustomer()->getId());
		$this->orderDocument->setOrder($this->order);
		$this->orderDocument->setAmount($amount);
		$this->orderDocument->setHash($hash);
		$this->modelManager->persist($this->orderDocument);
		$this->modelManager->flush($this->orderDocument);

		// Write file to disk
		$path = $this->getFilePath();
		file_put_contents($path, $pdf);
	}

	public function __set($name, $value = null)
	{
		$this->templateData[$name] = $value;
	}

	public function __get($name)
	{
		if (!isset($this->templateData[$name]) || !array_key_exists($name, $this->templateData)) {
			throw new \Exception('Variable ' . $name . ' not set.');
		}

		return $this->templateData[$name];
	}

	/**
	 * @return string
	 */
	private function getFilePath()
	{
		return Shopware()->OldPath() . 'files/documents/' . $this->orderDocument->getHash() . '.pdf';
	}

	/**
	 * Tries to find a translation of the config element with the given type and id
	 * in the language of the order.
	 *
	 * @param string $typo
	 * @param int    $elementId
	 * @param string $key
	 * @return string|null
	 */
	private function translateConfigDescription($type, $elementId, $key)
	{
		$translation = $this->getTranslation($type);
		if (isset($translation[$elementId]) && isset($translation[$elementId][$key])) {
			return $translation[$elementId][$key];
		}

		return null;
	}

	/**
	 * Looks up the 'type' in the translations using the language IDs of the shop
	 * in which the order was placed.
	 *
	 * @param string $type
	 * @return The translation of the given type for the set order.
	 */
	private function getTranslation($type)
	{
		$languageId = $this->order->getLanguageSubShop()->getId();
		$fallbackShop = $this->order->getLanguageSubShop()->getFallback();
		$fallbackLanguageId = ($fallbackShop !== null) ? $fallbackShop->getId() : null;

		return $this->translator->readBatchWithFallback($languageId, $fallbackLanguageId, $type);
	}

}

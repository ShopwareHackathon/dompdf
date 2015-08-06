<?php

namespace Shopware\Components\Document;

use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\Document\Type as DocumentType;
use Shopware\Models\Order\Document\Document as OrderDocumentModel;
use Shopware\Models\Order\Order as OrderModel;
use Shopware\Models\Payment\Payment;
use Symfony\Component\Config\Definition\Exception\Exception;

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
	 * @var array
	 */
	private $data = [];

	/**
	 * @var DocumentType
	 */
	private $documentType;

	/**
	 * @var \Shopware\Models\Order\Order
	 */
	private $order;

	/**
	 * @var string
	 */
	private $hash;

	/**
	 * @param DocumentType $type
	 */
	public function setDocumentType(DocumentType $type)
	{
		$this->documentType = $type;

		if ( $this->documentType->getNumbers()) {
			$this->__set('documentNumber', $this->documentType->getNumbers());
		}
	}

	/**
	 * @param OrderModel $order
     */
	public function setOrder(OrderModel $order)
	{
		$this->order = $order;
		$this->setCustomerNumber($this->order->getCustomer()->getBilling()->getNumber());
		$this->setOrderNumber($this->order->getNumber());
		$this->setDispatchMethod($this->order->getDispatch());
		$this->setPaymentMethod($this->order->getPayment());
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

	public function setItems()
	{

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
	public function renderPDF()
	{
		// new Order\Documents();
		$pdf = parent::renderPDF();

		$path = $this->getFilePath($this->documentType->getName(), $this->generateHash());
		$amount = $this->getOrderAmount();

		/**
		 * For cancellations
		 */
		if ($this->documentType->getId() == 4) {
			$amount *= -1;
		}

		$this->saveDocumentInDatabase($amount);

		$this->writePDFToDisk($path, $pdf);
	}

	public function __set($name, $value = null)
	{
		$this->data[$name] = $value;
	}

	public function __get($name)
	{
		if (!isset($this->data['name']) || !array_key_exists($name, $this->data)) {
			throw new \Exception('Variable ' . $name . ' not setted.');
		}
		return $this->data[$name];
	}

	/**
	 * Generates the document hash
	 * @return string
	 */
	private function generateHash()
	{
		$this->hash = md5(uniqid(rand()));
		return $this->hash;
	}

	/**
	 * @param string $documentTypeName
	 * @param string $hash
	 * @return string
	 */
	private function getFilePath($documentTypeName, $hash)
	{
		$path = Shopware()->OldPath() . 'files/documents/' . $documentTypeName . '/';
		if (!is_dir($path)) {
			mkdir($path);
		}
		return $path . $hash . '.pdf';
	}

	/**
	 * Helper function to get the correct invoice amount
	 * @return float
	 * @throws \Exception
	 */
	private function getOrderAmount()
	{
		$config = Shopware()->Container()->get('config');
		return $config->get('netto') == true ? round($this->order->getInvoiceAmountNet(), 2) : round($this->order->getInvoiceAmount(), 2);
	}

	/**
	 * @param string $numberRange
	 * @return int
	 */
	private function getCurrentDocumentNumber($numberRange)
	{
		$number = Shopware()->Db()->fetchRow("
            SELECT `number` as next FROM `s_order_number` WHERE `name` = ?", [$numberRange]
		);

		return $number['next'];
	}

	/**
	 * TODO: Document attributes, change to DBAL / doctrine
	 * @param $amount
	 * @return int
	 */
	private function saveDocumentInDatabase($amount)
	{
		try {
			Shopware()->Db()->beginTransaction();

			//generates and saves the next document number
			if ($this->isCancellation()) {
				$docId = $this->getCurrentDocumentNumber($this->documentType->getNumbers());
			} else {
				$docId = $this->getCurrentDocumentNumber($this->documentType->getNumbers());
				$docId = $this->increaseDocumentNumber($docId);
				$this->saveNextDocumentNumber($this->documentType->getNumbers(), $docId);
			}

			$sql = "
               INSERT INTO s_order_documents (`date`, `type`, `userID`, `orderID`, `amount`, `docID`,`hash`)
 	           VALUES ( NOW() , ? , ? , ?, ?, ?,?)
        	";

			Shopware()->Db()->query(
				$sql,
				[
					$this->documentType->getId(),
					$this->order->getCustomer()->getId(),
					$this->order->getId(),
					$amount,
					$docId,
					$this->hash
				]
			);
		} catch(Exception $e) {
			Shopware()->Db()->rollBack();
			throw new Exception(
				'Saving the order to the Database failed with following error message: ',
				$e->getMessage()
			);
		}
		Shopware()->Db()->commit();

		return Shopware()->Db()->lastInsertId();
	}

	/**
	 * @param $numberRange
	 * @param $number
	 */
	private function saveNextDocumentNumber($numberRange, $number)
	{
		Shopware()->Db()->query("
            UPDATE `s_order_number` SET `number` = ? WHERE `name` = ? LIMIT 1 ;",
			[$number, $numberRange]
		);
	}

	/**
	 * @param $path
	 * @param $pdf
	 */
	private function writePDFToDisk($path, $pdf)
	{
		file_put_contents($path, $pdf);
	}

	private function isCancellation()
	{
		return ($this->documentType->getId() == 4 ? true : false);
	}

	/**
	 * @param int $docId
	 * @return mixed
	 */
	private function increaseDocumentNumber($docId)
	{
		$docId++;
		return $docId;
	}
}

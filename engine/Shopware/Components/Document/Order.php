<?php

namespace Shopware\Components\Document;

/**
 * TODO
 *
 * @category  Shopware
 * @package   Shopware\Components\Document
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Order extends Base
{

	public function __construct()
	{
	}

	public function setDocumentType($type) {}

	public function setOrder() {}

	public function setSenderAddress() {}

	public function setReceiverAddress() {}

	public function setShopInfo() {}

	public function setCustomerNumber() {}

	public function setOrderNumber() {}

	public function setDate() {}

	public function setDocumentNumber() {}

	public function setItems() {}

	public function setDocumentComment() {}

	public function setCustomerComment() {}

	public function setPaymentMethod() {}

	public function setDispatchMethod() {}

	public function setContentInfo() {}

	public function setFooter() {}

	public function savePDF()
	{
		// new Order\Documents();
	}

	public function __set($name, $value = null)
	{

	}

}

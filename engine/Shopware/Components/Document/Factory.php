<?php

namespace Shopware\Components\Document;

/**
 * TODO
 *
 * @category  Shopware
 * @package   Shopware\Components\Document
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Factory
{

	public function get($type)
	{
		switch ($type) {
			case 'base':
				return new Base();
			case 'order':
				return new Order();
			default:
				throw Exception();
		}
	}

}

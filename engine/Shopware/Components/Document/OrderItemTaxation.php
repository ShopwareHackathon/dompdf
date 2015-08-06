<?php

namespace Shopware\Components\Document;

class OrderItemTaxation {

    const MODUS_DEFAULT_ARTICLE = 0;
    const MODUS_PREMIUM_ARTICLE = 1;
    const MODUS_VOUCHER = 2;
    const MODUS_CUSTOMERGROUP_DISCOUNT = 3;
    const MODUS_PAYMENT_SURCHARGE = 4; // payment surcharge or discount
    const MODUS_BUNDLE_DISCOUNT = 10;
    const MODUS_TRUSTED_SHOP_ARTICLE = 12;

    /**
     * @param Shopware\Models\Order\Detail $item
     * @param $net
     */
    function processItem($item, $net) {
        $order = $item->getOrder();
        $shipping = $order->getShipping();
        $customerGroupID = $order->getCustomer()->getGroup()->getId();

        $processedItem = Shopware()->Models()->toArray($item);

        if (in_array($item->getMode(), array(self::MODUS_DEFAULT_ARTICLE, self::MODUS_CUSTOMERGROUP_DISCOUNT, self::MODUS_PAYMENT_SURCHARGE, self::MODUS_BUNDLE_DISCOUNT, self::MODUS_TRUSTED_SHOP_ARTICLE))) {
            // Read tax for order item
            if ($item->getMode() == self::MODUS_CUSTOMERGROUP_DISCOUNT ||
                $item->getMode() == self::MODUS_PAYMENT_SURCHARGE) {
                if ($item->getTaxRate() == 0) {
                    // Discounts get tax from configuration
                    if (!empty(Shopware()->Config()->sTAXAUTOMODE)) {
                        $tax = $this->getMaxTaxRate($order);
                    } else {
                        $tax = Shopware()->Config()->sDISCOUNTTAX;
                    }
                    $processedItem['tax'] = $tax;
                } else {
                    $processedItem['tax'] = $item->getTaxRate();
                }
            } elseif (is_null($item->getTax())) {
                // Articles get tax per item configuration
                if ($item->getTaxRate() == 0) {
                    $processedItem['tax'] = $this->getTaxRepository()->getTaxRateByConditions(
                        Shopware()->Models()->getRepository('Shopware\Models\Article\Article')->find($item->getArticleId())->getTax()->getId(),
                        $shipping->getCountry()->getArea()->getId(),
                        $shipping->getCountry()->getId(),
                        $shipping->getCountry()->getState()->getId(),
                        $order->getCustomer()->getGroup()->getId()
                    );
                } else {
                    $processedItem['tax'] = $item->getTaxRate();
                }
            } else {
                // Bundles tax
                if ($item->getTaxRate() == 0) {
                    $processedItem['tax'] = $this->getTaxRepository()->getTaxRateByConditions(
                        $item->getTax()->getId(),
                        $shipping->getCountry()->getArea()->getId(),
                        $shipping->getCountry()->getId(),
                        $shipping->getCountry()->getState()->getId(),
                        $order->getCustomer()->getGroup()->getId()
                    );
                } else {
                    $processedItem['tax'] = $item->getTaxRate();
                }
            }

            if ($net == true) {
                $processedItem['net'] = round($item->getPrice(), 2);
                $processedItem['price'] = round($item->getPrice(), 2) * (1 + $item->getTax() / 100);
            } else {
                $processedItem['net'] = $item->getPrice() / (100 + $item->getTax()) * 100;
            }
        } elseif ($item->getMode() == self::MODUS_VOUCHER) {
            $ticketResult = Shopware()->Db()->fetchRow('
                SELECT * FROM s_emarketing_vouchers WHERE ordercode=?
                ', array($item['articleordernumber']));

            if ($item->getTaxRate() == 0) {
                if ($ticketResult['taxconfig'] == 'default' || empty($ticketResult['taxconfig'])) {
                    $processedItem['tax'] =  Shopware()->Config()->sVOUCHERTAX;
                    // Pre 3.5.4 behaviour
                } elseif ($ticketResult['taxconfig']=='auto') {
                    // Check max. used tax-rate from basket
                    $processedItem['tax'] = $this->getMaxTaxRate($order);
                } elseif (intval($ticketResult['taxconfig'])) {
                    // Fix defined tax
                    $temporaryTax = $ticketResult['taxconfig'];
                    $getTaxRate = Shopware()->Db()->fetchOne('
                        SELECT tax FROM s_core_tax WHERE id = $temporaryTax
                        ');
                    $processedItem['tax']  = $getTaxRate['tax'];
                } else {
                    $processedItem['tax']  = 0;
                }
            } else {
                $processedItem['tax'] = $item->getTaxRate();
            }
            if ($net == true) {
                $processedItem['net'] = $item->getPrice();
                $processedItem['price'] =  $item->getPrice() * (1 + $item->getTax() / 100);
            } else {
                $processedItem['net'] =  $item->getPrice() / (100 + $item->getTax()) * 100;
            }
        } elseif ($item->getMode() == self::MODUS_PREMIUM_ARTICLE) {
            $processedItem['tax'] = 0;
            $processedItem['net'] = 0;
        }

        $processedItem['amountNet'] = round($processedItem['net'] * $item->getQuantity(), 2);

        $processedItem['amount'] = $item->getPrice() * $item->getQuantity();

        return $processedItem;
    }

    /**
     * Get maximum used tax-rate in this order
     * @return int|string
     */
    public function getMaxTaxRate($order)
    {
        $shipping = $order->getShipping();
        $maxTaxRate = 0;
        foreach ($order->getItems as $item) {
            if ($item->getMode() == self::MODUS_DEFAULT_ARTICLE) {
                $taxRate = $item->getTaxRate();
                if ($taxRate == 0) {
                    $taxRate = Shopware()->Models()->getRepository('Shopware\Models\Tax\Tax')->getTaxRateByConditions(
                        $item->getTax()->getId(),
                        $shipping->getCountry()->getArea()->getId(),
                        $shipping->getCountry()->getId(),
                        $shipping->getCountry()->getState()->getId(),
                        $order->getCustomer()->getGroup()->getId()
                    );
                }
                if ($taxRate > $maxTaxRate) {
                    $maxTaxRate = $taxRate;
                }
            }
        }
        return $maxTaxRate;
    }

}





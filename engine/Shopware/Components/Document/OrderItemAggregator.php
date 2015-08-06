<?php

namespace Shopware\Components\Document;

class OrderItemAggregator {

    private $_amountNet;
    private $_amount;
    private $_tax;
    private $_discount;

    /**
     * @param array $item
     */
    public function addItem($item) {
        $this->_amountNet +=  $item["amountNet"];
        $this->_amount += $item["amount"];

        if ($item["tax"] != 0) {
            $this->_tax[number_format(floatval($item["tax"]), 2)] += round($item["amount"] / ($item["tax"] + 100) * $item["tax"], 2);
        }
        if ($item["amount"] <= 0) {
            $this->_discount += $item["amount"];
        }
    }

    public function getAmountNet() {
        return $this->_amountNet;
    }

    public function getAmount() {
        return $this->_amount;
    }

    public function getTax() {
        return $this->_tax;
    }

    public function getDiscount() {
        return $this->_discount;
    }

}

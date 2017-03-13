<?php
/* Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Giftcard
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Giftcard\Block\Adminhtml\Sales\Order;

class Totals extends \Magento\Framework\View\Element\Template{

    protected $giftcardorder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magedelight\Giftcard\Helper\Data
     */
    public $helper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Giftcard\Model\Order $giftcardorder
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, 
        \Magedelight\Giftcard\Model\Order $giftcardorder, 
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency, 
        \Magedelight\Giftcard\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->giftcardorder = $giftcardorder;
        $this->priceCurrency = $priceCurrency;        
        $this->helper = $helper;        
    }

    /**
     * Get totals source object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource() {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Create the giftcard totals summary
     *
     * @return $this
     */
    public function initTotals() {
        $order = $this->getSource();

        $giftcardTotal = $this->giftcardorder->getGiftcardTotal($order->getId());
        if ($giftcardTotal) {
            $giftCardCollection = $this->giftcardorder->getCollection()
                    ->addFieldToFilter('order_id', $order->getId());
            foreach ($giftCardCollection as $giftcardOrder) {
                $total = new \Magento\Framework\DataObject(
                        [
                    'code' => 'giftcard_discount_' . $giftcardOrder->getEntityId(),
                    'label' => __('Discounted from Gift Card (' . $this->helper->secureCode($giftcardOrder->getCode()) . ')'),
                    'value' => $this->priceCurrency->convert(-$giftcardOrder->getBaseDiscount()),
                    'base_value' => -$giftcardOrder->getBaseDiscount()
                        ]
                );
                $this->getParentBlock()->addTotal($total);
            }
        }
        return $this;
    }

}

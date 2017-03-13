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

namespace Magedelight\Giftcard\Model\Order\Invoice\Total;

class Giftcard extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
 
    
    protected $giftcardorder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    
    /**
     * @param \Magedelight\Giftcard\Model\Order $giftcardorder
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magedelight\Giftcard\Model\Order $giftcardorder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->giftcardorder = $giftcardorder;
        $this->priceCurrency = $priceCurrency;
    }
    
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);
        $order = $invoice->getOrder();
        
        $giftcardTotal = $this->giftcardorder->getGiftcardTotal($order->getId());
        
        $invoice->setGrandTotal($invoice->getGrandTotal() - $this->priceCurrency->convert($giftcardTotal));
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $giftcardTotal);

        return $this;
    }
}

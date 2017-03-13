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

namespace Magedelight\Giftcard\Model\Quote;

use Magedelight\Giftcard\Model\Quote;
use Magedelight\Giftcard\Model\QuoteAddress;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    
    /**
     * Discount calculation object
     *
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $quoteValidator;

    
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    
    /**
     * @var \Magedelight\Giftcard\Model\Quote
     */
    protected $quote;
    
    /**
     * @var \Magedelight\Giftcard\Model\QuoteAddress
     */
    protected $quoteAddress;
    
    protected $formatDiscount;
    
    protected $baseDiscount;

    
    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        Quote $quote,
        QuoteAddress $quoteAddress
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->quote = $quote;
        $this->quoteAddress = $quoteAddress;
        $this->quoteValidator = $validator;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        
        $address = $shippingAssignment->getShipping()->getAddress();
        
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        
        $this->baseDiscount = $this->quote->calculateDiscount($quote,$total);
        $this->formatDiscount =  $this->priceCurrency->convert($this->baseDiscount);       
        
        $total->addTotalAmount($this->getCode(), -$this->baseDiscount);
        $total->addBaseTotalAmount($this->getCode(), -$this->baseDiscount);        
        
        $quoteAddressObj = $this->quoteAddress->loadByAddressId($address->getAddressId());
        $quoteAddressObj->setQuoteAddressId($address->getAddressId());
        $quoteAddressObj->setGiftcardTotal($this->baseDiscount);
        $quoteAddressObj->getResource()->save($quoteAddressObj);        
 
        return $this;
    }
    
  
    
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => 'Giftcard Discount',
            'value' => -$this->baseDiscount
        ];
    }
    
    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Giftcard Discount');
    }
    
}

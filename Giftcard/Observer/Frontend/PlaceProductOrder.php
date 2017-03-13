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
namespace Magedelight\Giftcard\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magedelight\Giftcard\Model\Product\Type\Giftcard;
use Magedelight\Giftcard\Model\Code;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magedelight\Giftcard\Helper\Data as Helper;
use Magedelight\Giftcard\Model\Quote as GiftQuote;
use Magedelight\Giftcard\Model\QuoteAddress as GiftQuoteAddress;
use Magedelight\Giftcard\Model\OrderFactory;
use Magento\Customer\Model\Customer;

/**
 * Class PlaceProductOrder
 */
class PlaceProductOrder implements ObserverInterface
{
    
    /**
     * @var \Magedelight\Giftcard\Model\Code
     */
    protected $code;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productloader;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $helper;
    
    /**
     * @var \Magedelight\Giftcard\Model\Quote
     */
    protected $giftquote;
    
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $giftquoteaddress;
    
    /**
     * @var \Magedelight\Giftcard\Model\Order
     */
    protected $giftorder;
    
    /**
     * @var \Magento\Customer\Model\Customer
     */
    public $customer;
    
    /**
     * @param Magedelight\Giftcard\Model\Code $code
     * @param Magento\Catalog\Model\ProductFactory $productloader
     */
    public function __construct(
        Code $code,
        ProductFactory $productloader,
        DateTime $date,
        Helper $helper,
        GiftQuote $giftquote,
        GiftQuoteAddress $giftquoteaddress,
        \Magento\Quote\Model\Quote $quote,
        OrderFactory $giftorder,        
        Customer $customer
    ) {
        $this->code = $code;
        $this->productloader = $productloader;
        $this->date = $date;
        $this->helper = $helper;
        $this->giftquote = $giftquote;
        $this->quote = $quote;
        $this->giftquoteaddress = $giftquoteaddress;
        $this->giftorder = $giftorder;        
        $this->customer = $customer;
    }
    
    
    /**
     * Add Giftcard codes based on order placed
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {       
        if (!$this->helper->isActive())
        return;
        
        $orderInstance = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $items = $orderInstance->getAllVisibleItems();
        $customerId = $orderInstance->getCustomerId();
        $customer = $this->customer->load($customerId);
        $addressesCollection =  $quote->getAddressesCollection();
        foreach ($addressesCollection as $address){
        
            $discountLabel = '';
            $totalGiftCardBalance = 0;

            $giftquoteaddressObj = $this->giftquoteaddress->checkByAddressId($address->getAddressId());
            
            if(!$giftquoteaddressObj){
                continue;
            }
            
            $discountedFromGiftCard = $giftquoteaddressObj->getGiftcardTotal();
            if ($discountedFromGiftCard == 0){
                continue;
            }
            $giftCardCollection = $this->giftquote->getGiftCardCollection($quote->getId());

            $totalGiftCardBalance = $this->code->getTotalBalance($giftCardCollection);
            
            if ($totalGiftCardBalance < $discountedFromGiftCard)
                throw new \ErrorException(__('Not enough balance in gift card'));

            foreach ($giftCardCollection as $giftCard) {
                $giftCardBalance = $giftCard->getRemainingBalance();
                if ($discountedFromGiftCard >= $giftCardBalance) {
                    $discount = $giftCardBalance;
                } else {
                    $discount = $discountedFromGiftCard;
                }

                //in base currency
                $balance = $giftCardBalance - $discount;
                $giftCard->discount($balance);  
                
                $discountedFromGiftCard -= $discount;
                
                $this->giftorder->create()->addGiftCardByCode($orderInstance->getId(), $giftCard->getCode(), $discount);
                $this->createTransacton($giftCard,'Used',$customer,$orderInstance->getId());
                
                if ($totalGiftCardBalance <= 0 || $discountedFromGiftCard <= 0)
                    break;
            }
        }        
    }
    
    public function createTransacton($code,$action,$customer,$orderId){
        $comments = 'Code Used By User';
        $action_by = $customer->getName();
        $data = $this->helper->mapGiftcardTransaction($code,$action,$action_by,$comments,$orderId);
        $this->helper->addGicardftTransaction($data);
    }
    
}
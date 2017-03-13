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
use Magento\Customer\Model\Customer;
use Magedelight\Giftcard\Helper\Data;

/**
 * Class PlaceCodeOrder
 */
class PlaceCodeOrder implements ObserverInterface {

    /**
     * @var \Magedelight\Giftcard\Model\Code
     */
    protected $code;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productloader;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Magedelight\Giftcard\Helper\Data
     */
    protected $helper;    
    /**
     * @param Magedelight\Giftcard\Model\Code $code
     * @param Magento\Catalog\Model\ProductFactory $productloader
     */
    public function __construct(
        Code $code,
        ProductFactory $productloader,
        DateTime $date,
        Customer $customer,
        Data $helper
    ){
        $this->code = $code;
        $this->productloader = $productloader;
        $this->date = $date;
        $this->customer = $customer;
        $this->helper = $helper;
    }

    public function getCartProduct($id) {
        return $this->productloader->create()->load($id);
    }

    /**
     * Add Giftcard codes based on order placed
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        /** @var $orderInstance Order */
        $orderInstance = $observer->getEvent()->getOrder();
        $items = $orderInstance->getAllVisibleItems();
        
        
        foreach ($items as $item){
            if ($item->getProductType() == Giftcard::TYPE_GIFTCARD_PRODUCT) {                
                $customerId = $orderInstance->getCustomerId();
                $customer = $this->customer->load($customerId);
                
                $productOptions = $item->getProductOptions();
                $productOptions_data = $productOptions['additional_options'];
                $additional_data = [];
                foreach ($productOptions_data as $key => $single){
                    $label = $single['code'];
                    $value = $single['code_value'];
                    $additional_data[$label] = $value;
                }
                
                $product = $this->getCartProduct($item->getProductId());
                
                for ($i = 1; $i <= $item->getQtyOrdered(); $i++){
                    $itemsData = array();
                    $itemsData['code'] = uniqid();
                    $itemsData['balance'] = $item->getBasePrice();
                    $itemsData['remaining_balance'] = $item->getBasePrice();
                    $itemsData['customer_name'] = $customer->getName();
                    $itemsData['customer_email'] = $customer->getEmail();
                    $itemsData['recipient_name'] = isset($additional_data['recipient_name']) ? $additional_data['recipient_name'] : '';
                    $itemsData['recipient_email'] = isset($additional_data['recipient_email']) ? $additional_data['recipient_email'] : '';
                    $itemsData['recipient_message'] = isset($additional_data['message']) ? $additional_data['message'] : '';
                    $itemsData['lifetime'] = $product->getGiftcardLifetime();
                    $itemsData['order_id'] = $orderInstance->getId();
                    $itemsData['order_item_id'] = $item->getItemId();
                    $itemsData['giftcard_image'] = isset($additional_data['imageurl']) ? $additional_data['imageurl'] : '';
                    $itemsData['send_friend'] = isset($additional_data['send_friend']) ? $additional_data['send_friend'] : '';                                       
                    $itemsData['recipient_ship'] = isset($additional_data['recipient_ship']) ? $additional_data['recipient_ship'] : '';
                    
                    if($orderInstance->hasInvoices()){
                        $itemsData['status'] = 'sold';
                    }else{
                        $itemsData['status'] = 'pending';
                    }
                    
                    if (isset($itemsData['lifetime']) AND $itemsData['lifetime'] <= 0){
                        $itemsData['lifetime'] = $this->helper->getConfigLifetime();                        
                    }
                    
                    if (isset($itemsData['lifetime']) AND !empty($itemsData['lifetime'])){
                        $days = $itemsData['lifetime'];
                        $date = $this->date->gmtDate();
                        $date = strtotime("+$days day", strtotime($date));
                        $itemsData['expiration_time'] = $this->date->gmtDate(null, $date);
                    }
                    
                    $this->code->setData($itemsData);
                    $code = $this->code->save();
                    
                    if($code->getStatus() == 'sold'){
                        if((isset($additional_data['send_friend']) AND $additional_data['send_friend'] == 1)
                        AND 
                        (!isset($additional_data['recipient_ship']) AND $additional_data['recipient_ship'] != 1))
                        {
                            $this->helper->sendToFriend($itemsData);
                        }
                        else{
                            $this->helper->sendToCustomer($itemsData);
                        }
                    }
                    $this->createTransacton($code,'Create',$customer,$orderInstance->getId());
                }
            }
        }
    }
    
    public function createTransacton($code,$action,$customer,$orderId){
        $comments = 'New Code Generated By User';
        $action_by = $customer->getName();
        $data = $this->helper->mapGiftcardTransaction($code,$action,$action_by,$comments,$orderId);
        $this->helper->addGicardftTransaction($data);
    }
}

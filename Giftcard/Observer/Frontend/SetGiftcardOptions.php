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
use Magento\Quote\Model\Quote\ProductOptionFactory;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Model\CustomOptions\CustomOptionFactory;

/**
 * Class SetGiftcardOptions
 */
class SetGiftcardOptions implements ObserverInterface
{
    public $request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        if ($this->request->getFullActionName() == 'checkout_cart_add' || $this->request->getFullActionName() == 'checkout_cart_updateItemOptions')
        {
            $product = $observer->getEvent()->getProduct();
            $options = $this->request->getParam('additional_options');
            if (!empty($options) && $product->getTypeId() == 'giftcard')
            {
                $additionalOptions = array();
                foreach ($options as $key => $value)
                {   
                    if(isset($value) AND !empty($value) AND $key != 'price'){
                        $additionalOptions[] = $this->createCustomOptions($key,$value);
                    }
                }
                $product->addCustomOption('additional_options', serialize($additionalOptions));
            }            
        }
        return $this;
    }//end execute()
    
    public function createCustomOptions($key,$value){
        if($key == 'send_friend'){
            return array(
                'label' => 'Send To Friend',
                'value' => 'Yes',
                'code' => $key,
                'code_value' => $value
            );
        }
        elseif($key == 'customer_name'){
            return array(
                'label' => 'Customer Name',
                'value' => $value,
                'code' => $key,
                'code_value' => $value
            );
        }
        elseif($key == 'recipient_name'){
            return array(
                'label' => 'Recipient Name',
                'value' => $value,
                'code' => $key,
                'code_value' => $value
            );
        }
        elseif($key == 'recipient_email'){
            return array(
                'label' => 'Recipient Email',
                'value' => $value,
                'code' => $key,
                'code_value' => $value
            );
        }elseif($key == 'recipient_ship'){
            return array(
                'label' => 'Ship To Recipient Address',
                'value' => 'Yes',
                'code' => $key,
                'code_value' => $value
            );
        }
        elseif($key == 'message'){
            return array(
                'label' => 'Message',
                'value' => $value,
                'code' => $key,
                'code_value' => $value
            );
        }
        elseif($key == 'imageurl'){
            return array(
                'label' => 'Selected Template',
                'value' => '<a href="'.$value.'"><img width="70" src="'.$value.'" /></a>',
                'code' => $key,
                'code_value' => $value
            );
        }
        
    }    
}
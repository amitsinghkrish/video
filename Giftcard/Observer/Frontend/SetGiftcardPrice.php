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
 * Class SetGiftcardPrice
 */
class SetGiftcardPrice implements ObserverInterface
{
    public $request;
    public $product;
    public $productModel;
    public $loadProduct;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->request = $request;
        $this->productModel = $product;
    }

    public function execute(Observer $observer)
    {
        if ($this->request->getFullActionName() == 'checkout_cart_add' || $this->request->getFullActionName() == 'checkout_cart_updateItemOptions')
        {
            $item = $observer->getEvent()->getQuoteItem();
            $this->product = $observer->getEvent()->getQuoteItem()->getProduct();
            $this->loadProduct = $this->productModel->load($this->product->getId());
            
            $options = $this->request->getParam('additional_options');
            if (!empty($options) && $this->product->getTypeId() == 'giftcard')
            {
                $price = $this->getPriceByItem($this->product);
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
        return $this;
    }//end execute()
    
    public function getPriceByItem()
    {
        $options = $this->request->getParam('additional_options');
        $additionalOptions = array();
        foreach ($options as $key => $value)
        {
            if($key == 'price'){
                if($this->validateCustomPrice($value)){
                    $price = $value;
                }
                else{
                    throw new \ErrorException(__('Price is not Valid.'));
                }
            }
        }
        
        //use $item to determine your custom price.
        return $price;
    }
    
    public function validateCustomPrice($price){
        $priceType = $this->loadProduct->getData('giftcard_price_type');
        if($priceType == 1){
            $originalPrice = $this->loadProduct->getData('giftcard_balance');
            if($originalPrice === $price){
                return true;
            }
        }
        else{
            $minPrice = $this->loadProduct->getData('giftcard_price_min');
            $maxPrice = $this->loadProduct->getData('giftcard_price_max');
            if(($minPrice <= $price) && ($price <= $maxPrice)){
                return true;
            }
        }
        return false;
    }
}
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

namespace Magedelight\Giftcard\Block\Giftproduct;

use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Options extends \Magento\Framework\View\Element\Template {

    public $_registry;
    
    public $request;
    
    public $helper;   
     
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public $priceCurrency;
    
    /**
     * Template Factory
     *
     * @var FactoryInterface
     */
    protected $templateFactory;

    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magedelight\Giftcard\Helper\Data $helper,
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $registry, 
        \Magento\Framework\App\Request\Http $request,
        FactoryInterface $templateFactory,
        array $data = []
    ){
        $this->_registry = $registry;
        parent::__construct($context, $data);
        $this->request = $request;
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;        
        $this->templateFactory = $templateFactory;     
    }

    public function _prepareLayout(){
        return parent::_prepareLayout();
    }

    public function getCurrentCategory(){
        return $this->_registry->registry('current_category');
    }

    public function getCurrentProduct(){
        return $this->_registry->registry('current_product');
    }

    public function isGiftcardProduct(){
        if($this->getCurrentProduct()){
            if($this->getCurrentProduct()->getTypeId() == 'giftcard'){
                return true;
            }
        }
        return false;
    }
    
    public function getFormattedPrice($price){
        return $this->priceCurrency->format($price);
    }
    
    public function getFormattedPriceContainer($price){
        return $this->priceCurrency->format($price,false);
    }
    
    public function send(){
        $this->helper->sendGiftcardMail();
    }
    
    public function getGiftTemplate($itemsData)
    {
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($itemsData);
        $template = $this->templateFactory->get('magedelight_giftcard_email', null)
            ->setVars(['data' => $postObject])
            ->setOptions([
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]);
        return $body = $template->processTemplate();
    }
    
    public function getAjaxUrl(){
        return $this->getUrl('giftcard/product/preview');
    }
    public function getLifeTime(){
        $product = $this->getCurrentProduct();        
        $lifetime = $product->getData('giftcard_lifetime');
        $days = $this->helper->getConfigLifetime().' days';        
        if($lifetime > 0){
            $days = $lifetime.' days';
        }
        return $days;
    }
    
    public function getLifeTimeNum(){
        $product = $this->getCurrentProduct();
        $lifetime = $product->getData('giftcard_lifetime');
        $days = $this->helper->getConfigLifetime();
        if($lifetime > 0){
            $days = $lifetime;
        }
        return $days;
    }
    public function getItemsData(){
        $itemId = $this->getRequest()->getParam('id');
        if(isset($itemId) && !empty($itemId)){                              
                $items = $this->helper->cart->getQuote()->getAllVisibleItems();
                foreach($items as $single){
                    if($itemId == $single->getId()){
                        $additionalOptions = $single->getOptionByCode('additional_options');
                        $options = unserialize($additionalOptions->getValue());
                        $additional_data = [];
                        foreach ($options as $key => $singleItem){
                            $label = $singleItem['code'];
                            $value = $singleItem['code_value'];
                            $additional_data[$label] = $value;
                        }
                        if(!empty($additional_data)){
                            $additional_data['price'] = $single->getBasePrice();
                            return $additional_data;
                        }
                    }
                }
        }
        return false;
    }
}

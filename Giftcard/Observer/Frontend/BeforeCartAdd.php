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
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class SetGiftcardPrice
 */
class BeforeCartAdd implements ObserverInterface
{
    public $request;
    
    public $product;
    
    public $helper;
    
    public $response;
    
    public $messageManager;
    
    public $catalogProductHelper;
    
    public $productloader;
    
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        ResponseInterface $response,
        Data $helper,
        ManagerInterface $messageManager,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Catalog\Model\ProductFactory $productloader
    ){
        $this->request = $request;
        $this->helper = $helper;
        $this->response = $response;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->messageManager = $messageManager;
        $this->productloader = $productloader;
    }

    public function execute(Observer $observer)
    {
        if ($this->request->getFullActionName() == 'checkout_cart_add')
        {
            $productId = $this->request->getParam('product');
            $product = $this->productloader->create()->load($productId);
            if($product->getTypeId() == 'giftcard'){
                $options = $this->request->getParam('additional_options');
                if(empty($options))
                {
                    $url = $this->catalogProductHelper->getProductUrl($productId);
                    $this->request->setParam('product', false);
                    $this->request->setParam('return_url', $url);
                    $this->messageManager->addNoticeMessage(__('Please Specify the required Options.'));
                }
            }
        }
        return $this;
    }//end execute()
    
}
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

namespace Magedelight\Giftcard\Controller\Cart;

use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart as CustomerCart;

class GiftcardPost extends \Magento\Checkout\Controller\Cart
{
    /**
     * Giftcard Quote
     *
     * @var \Magedelight\Giftcard\Model\Quote
     */
    protected $giftcard_quote;
    
    protected $cart;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->giftcard_quote = $this->_objectManager->create('Magedelight\Giftcard\Model\Quote');
        $this->cart = $cart;
    }
    
    public function execute()
    {
        if ($this->getRequest()->getParam('remove') == 1) {
            $this->_forward('giftcardRemove');
            return;
        }
        
        $giftcardCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('giftcard_code'));
        
        try {
            $grandTotal = $this->cart->getQuote()->getBaseGrandTotal();
                $quoteId = $this->cart->getQuote()->getId();
                if(!empty($quoteId) AND !empty($this->cart->getQuote()->getItemsCount())){
                    if($grandTotal > 0){
                        if($this->giftcard_quote->addGiftCardByCode($quoteId, $giftcardCode)){
                            $this->messageManager->addSuccess(__("Gift card $giftcardCode was applied."));
                        }else{
                            $this->messageManager->addErrorMessage(__("Gift card $giftcardCode is not valid."));
                        }
                    }
                    else{
                        $this->messageManager->addErrorMessage(__("Your Cart Total is Zero."));
                    }
                }
                else{
                    $this->messageManager->addErrorMessage(__("Cart is empty."));
                }
                $this->_checkoutSession->getQuote()->save();
                $this->cart->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            //$this->messageManager->addError(__('We cannot apply the giftcard code.'));
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return $this->_goBack();
    }
       
}

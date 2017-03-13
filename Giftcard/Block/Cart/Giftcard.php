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

namespace Magedelight\Giftcard\Block\Cart;

use Magedelight\Giftcard\Model\Quote;

class Giftcard extends \Magento\Checkout\Block\Cart\AbstractCart
{
    
    protected $quoteFactory;
    
    protected $currentCode = '';
    
    public $helper;
    
    /**
     * @param Quote $quoteFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magedelight\Giftcard\Helper\Data $helper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Quote $quoteFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magedelight\Giftcard\Helper\Data $helper,
        array $data = []
    ){
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
        $this->quoteFactory = $quoteFactory;
        $this->helper = $helper;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getGiftcardCode()
    {
        return $this->currentCode;
    }
    
    public function getAppliedGiftCards()
    {
        $quoteId = $this->getQuote()->getId();
        return $this->quoteFactory->getGiftCardCollection($quoteId);
    }
    
    public function isGiftcardAllowed(){
        if($this->helper->isActive() && $this->helper->isGiftcardForProducts()){
            return true;
        }
        return false;
    }
    
}

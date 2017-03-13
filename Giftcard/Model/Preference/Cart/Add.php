<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Giftcard\Model\Preference\Cart;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{
    
    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|\Magento\Framework\Controller\Result\Redirect
   
    protected function goBack($backUrl = null, $product = null)
    {
        if($this->getRequest()->getParam('gift_return_url')){
            parent::goBack($this->getRequest()->getParam('gift_return_url'),$product);
        }
        else{
            parent::goBack($backUrl,$product);
        }
    }  */
}

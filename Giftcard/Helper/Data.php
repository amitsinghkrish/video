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

namespace Magedelight\Giftcard\Helper;

use Magedelight\Giftcard\Model\CodeFactory;
use Magedelight\Giftcard\Model\QuoteFactory;
use Magedelight\Giftcard\Model\HistoryFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\View\Element\Template;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    public $code;
    public $quote;
    public $_scopeInterface;
    public $transportBuilder;
    public $productModel;
    public $priceCurrency;
    public $historyFactory;
    public $cart;
    public $date;
    public $templateBlock;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $urlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    public $catalogHelperImage;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CodeFactory $code,
        QuoteFactory $quote,
        HistoryFactory $historyFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        Image $catalogHelperImage,
        Product $productModel,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Cart $cart,
        Template $templateBlock
    ){
        parent::__construct($context);
        $this->code = $code;
        $this->quote = $quote;
        $this->_scopeInterface = $context->getScopeConfig();
        $this->transportBuilder = $transportBuilder;
        $this->catalogHelperImage = $catalogHelperImage;
        $this->productModel = $productModel;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->priceCurrency = $priceCurrency;
        $this->historyFactory = $historyFactory;
        $this->cart = $cart;
        $this->date = $date;
        $this->templateBlock = $templateBlock;
    }

    public function getUrl($route = '', $params = []){
        return $this->urlBuilder->getUrl($route, $params);
    }
    
    /**
     * Retrieve Mail From for the current store
     *
     * @return int
     */
    public function getConfigEmail()
    {
        return $this->_scopeInterface->getValue(
            'magedelight/email_setting/email_from',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve Default Giftcard Image
     *
     * @return int
     */
    public function getDefaultGiftImage()
    {
        return $this->templateBlock->getViewFileUrl("Magedelight_Giftcard::images/default_image.png");
    }    
    
    /**
     * Retrieve Mail From for the current store
     *
     * @return int
     */
    public function getConfigLifetime()
    {
        return $this->_scopeInterface->getValue(
            'magedelight/general/lifetime',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getFormattedPrice($price,$includeContainer = true){
        return $this->priceCurrency->format($price,$includeContainer);
    }
    
    public function getConvertedPrice($price){
        return $this->priceCurrency->convert($price);
    }

    public function isActive(){
        return $this->_scopeInterface->getValue(
            'magedelight/general/enable_giftcard', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function isGiftcardForProducts(){
        $option = $this->_scopeInterface->getValue(
            'magedelight/general/giftcard_in_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if(!$option){
           $cartItems = $this->cart->getQuote()->getAllVisibleItems();
           foreach($cartItems as $cartItem){
               if($cartItem->getProduct()->getTypeId() == \Magedelight\Giftcard\Model\Product\Type\Giftcard::TYPE_GIFTCARD_PRODUCT){
                   return false;
               }
           } 
        }
        return true;
    }
    
    public function secureCode($code){
        $lastFour = substr($code, -4);       
        return $secureCode = 'XXXX'.$lastFour;
    }

    public function sendGiftcardMail($senderEmail, $senderName, $itemsData){
        if(empty($itemsData['expiration_time'])){
            $itemsData['expiration_time'] = 'Unlimited Days';
        }
        if(empty($itemsData['recipient_message'])){
            $itemsData['recipient_message'] = 'Not Available';
        }
        if(empty($itemsData['giftcard_image'])){
            $itemsData['giftcard_image'] = $this->getDefaultGiftImage();
        }
        
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($itemsData);
        $postObject->setBalance($this->getFormattedPrice($itemsData['balance']));
        $postObject->setRemainingBalance($this->getFormattedPrice($itemsData['remaining_balance']));        
        $fromMail = 'general';
        if($this->getConfigEmail()){
            $fromMail = $this->getConfigEmail();
        }
        $this->transportBuilder
            ->setTemplateIdentifier('magedelight_giftcard_email')
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars(['data' => $postObject])
            ->setFrom($fromMail)
            ->addTo($senderEmail, $senderName);

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    public function in_range($number, $min, $max, $inclusive = FALSE) {
        if (is_int($number) && is_int($min) && is_int($max)) {
            return $inclusive ? ($number >= $min && $number <= $max) : ($number > $min && $number < $max);
        }
        return FALSE;
    }

    public function sendToFriend($itemsData) {
        $senderEmail = $itemsData['recipient_email'];
        $senderName = $itemsData['recipient_name'];
        $this->sendGiftcardMail($senderEmail, $senderName, $itemsData);
    }

    public function sendToCustomer($itemsData){
        /*$senderEmail = $itemsData['customer_email'];
        $senderName = $itemsData['customer_name'];*/
        $senderEmail = $itemsData['recipient_email'];
        $senderName = $itemsData['recipient_name'];
        $this->sendGiftcardMail($senderEmail, $senderName, $itemsData);
    }

    public function getCartProduct($id){
        return $this->productModel->load($id);
    }

    public function getGiftcardPrice($product){
        if ($product->getGiftcardPriceType() == '2') {
            $minPrice = $this->getFormattedPrice($product->getGiftcardPriceMin());
            $maxPrice = $this->getFormattedPrice($product->getGiftcardPriceMax());
            ob_start();
            ?>
            <div class="price-box">
                <p class="min-price">
                    <span class="price-label">From</span>
                    <span class="price-container price-final_price">
                        <span class="price-wrapper " data-price-type="finalPrice" data-price-amount="<?php echo $product->getGiftcardPriceMin(); ?>" id="product-from-price-<?php echo $product->getEntityId(); ?>">
                            <span class="price"><?php echo $minPrice; ?></span>
                        </span>
                    </span>
                </p>
                <p class="max-price">
                    <span class="price-label">To</span>
                    <span class="price-container price-final_price">
                        <span class="price-wrapper " data-price-type="finalPrice" data-price-amount="<?php echo $product->getGiftcardPriceMax(); ?>" id="product-to-price-<?php echo $product->getEntityId(); ?>">
                            <span class="price"><?php echo $maxPrice; ?></span>
                        </span>
                    </span>
                </p>
            </div>
            <?php
            return $result = ob_get_clean();            
        }else{
            $price = $this->getFormattedPrice($product->getGiftcardBalance());
            ob_start();
            ?>
            <div class="price-box price-final_price" data-role="priceBox">
                <span class="price-container price-final_price">
                    <span id="product-price-<?php echo $product->getEntityId(); ?>" class="price-wrapper " data-price-type="finalPrice" data-price-amount="<?php echo $product->getGiftcardBalance(); ?>">
                        <span class="price"><?php echo $price; ?></span>
                    </span>
                </span>
            </div>
            <?php
            return $result = ob_get_clean();
        }
    }
    
    public function addGicardftTransaction($data){
        $history = $this->historyFactory->create();
        $history->setData($data);
        $history->getResource()->save($history);
    }
    
    public function mapGiftcardTransaction($code,$action,$action_by,$comments,$orderId = '0'){
        $data = [];
        $data['code_id'] = $code->getId();
        $data['action'] = $action;
        $data['value'] = $code->getRemainingBalance();
        $data['status'] = $code->getStatus();
        $data['order'] = $orderId;
        $data['comments'] = $comments;
        $data['action_by'] = 'Created By '.$action_by;        
        return $data;
    }
    
    public function getHistoryByCode($codeId){
        $collection = $this->historyFactory->create()->getCollection()
                ->addFieldToFilter('code_id', $codeId)
                ->setOrder('history_id','DESC');

        return $collection;
    }
    
    public function resetCodes(){
        $collection = $this->code->create()->getCollection();
        foreach($collection as $codes){
            $expireTime = $codes->getExpirationTime();
            $remainingBalance = $codes->getRemaining_balance();
            if(isset($expireTime) AND !empty($expireTime)){
                $date = $this->date->gmtDate();
                if($expireTime < $date){
                    $codes->setStatus(\Magedelight\Giftcard\Model\Code::STATUS_EXPIRED);
                    $codes->getResource()->save($codes);
                }
                else{
                    $status = $codes->getStatus();
                    if($status == \Magedelight\Giftcard\Model\Code::STATUS_EXPIRED){
                        $codes->setStatus(\Magedelight\Giftcard\Model\Code::STATUS_PENDING);
                        $codes->getResource()->save($codes);
                    }
                }
            }
            if($remainingBalance <= 0){
                $codes->setStatus(\Magedelight\Giftcard\Model\Code::STATUS_INACTIVE);
                $codes->getResource()->save($codes);
            }
        }
    }
}
?>
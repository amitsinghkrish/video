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

namespace Magedelight\Giftcard\Model;

use Magento\Framework\Model\AbstractModel;
use Magedelight\Giftcard\Model\Code;

/**
 * Class Quote
 *
 * @package Magedelight\Giftcard\Model
 */
class Quote extends AbstractModel
{
    /* Giftcard cache tag
     */
    const CACHE_TAG = 'entity_id';
     
    /**
     * @var string
     */
    protected $_cacheTag = 'entity_id';
    
    protected $codeFactory;
    
    private $__giftCardCollection = [];
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'entity_id';
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
    */
    
    public function __construct(
        Code $codeFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context,$registry,$resource,$resourceCollection,$data);
        $this->codeFactory = $codeFactory;
    }
    
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct( 
    )
    {
        $this->_init('Magedelight\Giftcard\Model\ResourceModel\Quote');
    }
    
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    
    
    public function isGiftCardCodeValid($quoteId, $giftCardCode)
    {
        $codeLength = strlen($giftCardCode);
        $isCodeLengthValid = $codeLength > 0;

        if (!$isCodeLengthValid) {
            throw new \ErrorException(__('Invalid Gift Card code.'));
        }
        
        $giftCard = $this->codeFactory->loadByCode($giftCardCode);
        if(!$giftCard->getCodeId() || !($giftCard->isSold() || $giftCard->isActive())){
            throw new \ErrorException(__('Invalid Gift Card code.'));
        }

        if ($this->isAddedToQuote($quoteId, $giftCard->getCodeId())) {
            throw new \ErrorException(__('This Gift Card code is already added.'));
        }

        return true;
    }
    
    public function isAddedToQuote($quoteId, $giftCardId)
    {
        $giftCardCollection = $this->getCollection()
            ->addFieldToFilter('giftcard_id', $giftCardId)
            ->addFieldToFilter('quote_id', $quoteId);
        return count($giftCardCollection) == 1;
    }
    
    public function addGiftCardByCode($quoteId, $giftCardCode)
    {
        if (!$this->isGiftCardCodeValid($quoteId, $giftCardCode)){
            return false;
        }

        $this->codeFactory->loadByCode($giftCardCode);

        $this->setQuoteId($quoteId);
        $this->setGiftcardId($this->codeFactory->getCodeId());
        $this->getResource()->save($this);
        
        if (!$this->getEntityId()) {
            return false;
        }

        return true;
    }
    
    public function removeGiftCardArrayFromQuote($quoteId, array $codes)
    {
        if (count($codes) == 0) {
            return true;
        }

        foreach ($codes as $code) {
            $this->removeGiftCardByCode($quoteId, $code);
        }

        return true;
    }

    public function removeGiftCardByCode($quoteId, $code)
    {
        $giftCard =  $this->codeFactory->loadByCode($code);
        if (!$giftCard->getCodeId()){
            return false;
        }

        $giftCardQuote = $this->getCollection()
            ->addFieldToFilter('giftcard_id', $giftCard->getCodeId())
            ->addFieldToFilter('quote_id', $quoteId);

        if ($giftCardQuote->count() == 0) {
            return false;
        }

        foreach ($giftCardQuote as $item) {
            $item->getResource()->delete($item);
        }

        return true;
    }
    
    public function getGiftCardCollection($quoteId)
    {
        if (!isset($this->__giftCardCollection[$quoteId])) {
            $quoteCollection = $this->getCollection()
                ->addFieldToFilter('quote_id', $quoteId);
            if (count($quoteCollection) == 0) {
                $this->__giftCardCollection[$quoteId] = null;
            }
            $gifCardIds = array();
            foreach ($quoteCollection as $item) {
                $gifCardIds[] = $item->getGiftcardId();
            }
            $giftCardCollection = $this->codeFactory->getCollection()
                ->addFieldToFilter('code_id', array('in' => $gifCardIds))
                ->addFieldToFilter('status',  array('in' => $this->codeFactory->getAllActiveStatus()))
                ->addFieldToFilter('remaining_balance', array('gt' => 0));

            $this->__giftCardCollection[$quoteId] = $giftCardCollection;
        }

        return $this->__giftCardCollection[$quoteId];
    }
    
    public function calculateDiscount($quote,$totalObj){
        $giftCardDiscount = 0;
        $total = 0;
        $totals = $totalObj->getAllTotalAmounts();
        
        if (count($totals) > 0) {
            foreach ($totals as $amount) {
                $total+=$amount;
            }
        }
        
        if ($total > 0) {
            $giftCardDiscount = 0;
            $appliedGiftCardCollection = $this->getGiftCardCollection($quote->getId());
            if (count($appliedGiftCardCollection) > 0){
                foreach ($appliedGiftCardCollection as $giftCard){
                    $balance = $giftCard->getRemainingBalance();
                    $giftCardDiscount += $balance;
                }
            }
            //if total less than gift cards balance
            if ($giftCardDiscount > $total)
                $giftCardDiscount = $total;
        }
        
        return $giftCardDiscount;
    }
    
}
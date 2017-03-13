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

/**
 * Class Code
 *
 * @package Magedelight\Giftcard\Model
 */
class Code extends AbstractModel
{
    /* Giftcard cache tag
     */
    const CACHE_TAG = 'giftcard_code';
    
    const GIFT_CARD_CODE_MAX_LENGTH = 255;
    
    const STATUS_SOLD = 'sold';
    
    const STATUS_INACTIVE = 'inactive';
    
    const STATUS_PENDING = 'pending';
    
    const STATUS_ACTIVE = 'active';
    
    const STATUS_EXPIRED = 'expired';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'giftcard_code';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'giftcard_code';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Giftcard\Model\ResourceModel\Code');
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
    
    public function getSoldStatus(){
        return self::STATUS_SOLD;
    }
     
    public function getAllActiveStatus(){
        return array(self::STATUS_SOLD,self::STATUS_ACTIVE);
    }
    
    /**
     * Prepare code's statuses.
     *
     * @return array
     */    
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_INACTIVE => __('Inactive'), 
            self::STATUS_SOLD => __('Sold'),
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_EXPIRED => __('Expired')
        ];
    }
    
    public function isSold()
    {
        return $this->getStatus() == self::STATUS_SOLD;
    }
    
    public function isActive()
    {
        return $this->getStatus() == self::STATUS_ACTIVE;
    }
    
    public function loadByCode($code)
    {
        if (empty($code) || strlen($code) > self::GIFT_CARD_CODE_MAX_LENGTH)
            return $this;

        $collection = $this->getCollection()
            ->addFieldToFilter('code', $code)
            ->setPageSize(1);
        if ($collection->count() == 0)
            return $this;

        return parent::load($collection->getFirstItem()->getId());
    }
    
    /**
     * Sum Giftcard balance based on giftcard collection
     */
    public function getTotalBalance($giftCardCollection)
    {
        $totalGiftCardBalance = 0;
        foreach ($giftCardCollection as $giftCard) {
            $totalGiftCardBalance += $giftCard->getRemainingBalance();
        }
        return $totalGiftCardBalance;
    }
    
    /**
     * Set Giftcard balance 
     * 
     */ 
    public function discount($balance)
    {
        $this->setRemainingBalance($balance);
        if ($balance <= 0) {
            $this->setStatus(self::STATUS_INACTIVE);
        }
        $this->save();
    }
    
    public function calcExpiredAt()
    {
        $lifeTime = $this->getLifetime();
        if ($lifeTime == 0)
            return '';

        return date('Y-m-d H:i:s', strtotime('+'.$lifeTime.'days'));
    }
    
    public function setExpiredAt($date = '')
    {
        if($date == ''){
            $date = $this->calcExpiredAt();
        }
        $this->setExpirationTime($date);
    }
    
}
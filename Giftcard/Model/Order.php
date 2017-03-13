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
class Order extends AbstractModel
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
        $this->_init('Magedelight\Giftcard\Model\ResourceModel\Order');
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
    
    public function addGiftCardByCode($orderId, $giftCardCode, $baseDiscount = 0)
    {
        if ($this->isAddedToOrder($orderId, $giftCardCode)) {
            throw new \ErrorException(__('Something is wrong. Gift card already exist in order!'));
        }

        $this->setOrderId($orderId);
        $this->setCode($giftCardCode);
        $this->setDiscount($baseDiscount);
        $this->setBaseDiscount($baseDiscount);
        $this->save();

        if (!$this->getId()) {
            return false;
        }

        return true;
    }

    public function isAddedToOrder($orderId, $giftCardCode)
    {
        $giftCardCollection = $this->getCollection()
            ->addFieldToFilter('code', $giftCardCode)
            ->addFieldToFilter('order_id', $orderId);
        return count($giftCardCollection) == 1;
    }
    
    public function getGiftcardTotal($orderId)
    {
        $total = 0;
        $giftCardCollection = $this->getCollection()
            ->addFieldToFilter('order_id', $orderId);
        foreach($giftCardCollection as $giftcardOrder){
            $total += $giftcardOrder->getBaseDiscount();
        }
        return $total;
    }
    
}
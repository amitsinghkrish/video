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

/**
 * Class SetGiftcardPrice
 */
class SetGiftcardSaleble implements ObserverInterface
{
    public $request;
    public $product;
    
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        if ($this->request->getFullActionName() == 'catalog_category_view')
        {
            $product = $observer->getEvent()->getProduct();
            $saleble = $observer->getEvent()->getSalable();
	    
            if($product->getTypeId() == 'giftcard')
            {
                /*$saleble->setIsSalable(false);
                $salable = $product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
                if ($salable && $product->hasData('is_salable')) {
                    $salable = $product->getData('is_salable');
                }
                $product->setIsSaleable($salable);*/
            }
        }
        return $this;
    }//end execute()   
}
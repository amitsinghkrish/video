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

namespace Magedelight\Giftcard\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;

class Giftcard extends \Magento\Catalog\Model\Product\Type\AbstractType {

    const TYPE_GIFTCARD_PRODUCT = 'giftcard';

    /**
     * Delete data specific for Giftcard product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product) {
        
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isVirtual($product) {
        $productOptionString = $product->getCustomOption('additional_options');
        if(isset($productOptionString) AND !empty($productOptionString) AND $product->getTypeId() == self::TYPE_GIFTCARD_PRODUCT){
            $productOption = unserialize($productOptionString->getValue());
            foreach ($productOption as $key => $single){
                if(($single['code'] == 'send_friend' AND $single['code_value'] == 1) AND ($single['code'] != 'recipient_ship' AND $single['code_value'] != 1)){
                    return true;
                }
            }
        }
        return false;
    }

}

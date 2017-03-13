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

namespace Magedelight\Giftcard\Controller\Product;

use Magedelight\Giftcard\Block\Giftproduct\Options;

class Preview extends \Magento\Framework\App\Action\Action
{
    
    public $options;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Options $options
    ) {
        parent::__construct($context);
        $this->options = $options;
    }
    
    public function execute()
    {
        if($this->getRequest()->isAjax()){
            
            $data = $this->getRequest()->getParams();            
            $itemsData = array();
            $itemsData['code'] = $data['code'];
            $itemsData['balance'] = $this->options->getFormattedPriceContainer($data['additional_options']['price']);
            $itemsData['remaining_balance'] = $this->options->getFormattedPriceContainer($data['additional_options']['price']);
            $itemsData['customer_name'] = $data['additional_options']['customer_name'];
            $itemsData['recipient_name'] = $data['additional_options']['recipient_name'];
            $itemsData['recipient_email'] = $data['additional_options']['recipient_email'];
            $itemsData['recipient_message'] = $data['additional_options']['message'];
            $itemsData['expiration_time'] = $data['expiration_time'];
            $itemsData['giftcard_image'] = $data['imageurl'];
            $myHtml = $this->options->getGiftTemplate($itemsData);
            $this->getResponse()
                ->setHeader('Content-Type', 'text/html')
                ->setBody($myHtml);
            return;
        }
    }
       
}

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

namespace Magedelight\Giftcard\Block\Customer;

use Magedelight\Giftcard\Helper\Data;
use Magento\Framework\HTTP\Header;

class Giftcard extends \Magento\Framework\View\Element\Template {
    
    public $coreRegistry;
    
    public $helper;
    
    public $http;
    
    public $orderRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        Header $http,
        Data $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->helper = $helper;
        $this->http = $http;
        $this->orderRepository = $orderRepository;
    }

    public function getCode()
    {
        return $this->coreRegistry->registry('code');
    }
    
    public function getCodeHistory($codeId){
        return $this->helper->getHistoryByCode($codeId);
    }
    
    public function getHelper(){
        return $this->helper;
    }
    
    public function getReferer(){
        return $this->http->getHttpReferer();
    }
    public function getOrderIncrementId($orderId){        
        if($orderId != 0){
            $order = $this->orderRepository->get($orderId);
            return $orderIncrementId = $order->getIncrementId();
        }
        return 0;
    }
}

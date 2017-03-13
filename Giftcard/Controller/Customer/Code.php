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

namespace Magedelight\Giftcard\Controller\Customer;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magedelight\Giftcard\Model\CodeFactory;


class Code extends \Magento\Framework\App\Action\Action
{
    public $pageFactory;
    
    public $session;
    
    public $coreRegistry;
    
    public $codeFactory;
    
    public function __construct(        
        Context $context,
        Session $session, 
        PageFactory $pageFactory,
        Registry $coreRegistry,
        CodeFactory $codeFactory
    ){
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->coreRegistry = $coreRegistry;
        $this->codeFactory = $codeFactory;        
    }
    
    public function execute()
    {
        echo '<pre>';
        print_r($_SERVER['REQUEST_METHOD']);
        echo '</pre>';
        
        if(!$this->session->isLoggedIn()) {
            $this->_redirect("customer/account/login");
        }
       
        try {
            $data = $this->getRequest()->getPostValue();
            if (isset($data['giftcard_code']) AND !empty($data['giftcard_code'])){
                $code = $this->codeFactory->create()->loadByCode($data['giftcard_code']);
                if(!empty($code->getData())){
                    $this->coreRegistry->register('code', $code);
                }
                else{
                    
                    $this->messageManager->addError(__('Invalid Giftcard Code'));
                    return $this->_redirect("*/*/index");
                    
                }
            }
            else{
                return $this->_redirect("*/*/index");
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addException(
                    $e, __('%1', $e->getMessage())
            );
            return $this->_redirect("*/*/index");
            
        } catch (\Exception $e) {
            $this->messageManager->addException(
                    $e, __('%1', $e->getMessage())
            );
            return $this->_redirect("*/*/index");            
        }        
        $page_object = $this->pageFactory->create();
        return $page_object;
    }
}

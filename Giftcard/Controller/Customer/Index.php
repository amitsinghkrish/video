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


class Index extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;
    protected $session;
    public function __construct(Context $context,Session $session, PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        return parent::__construct($context);
    }
    
    public function execute()
    {
        if(!$this->session->isLoggedIn()) {
            $this->_redirect("customer/account/login");
        }
        $page_object = $this->pageFactory->create();
        return $page_object;
    }
}

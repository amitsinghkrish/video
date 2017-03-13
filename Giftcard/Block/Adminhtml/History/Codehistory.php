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

namespace Magedelight\Giftcard\Block\Adminhtml\History;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Codehistory extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magedelight\Giftcard\Model\HistoryFactory
     */
    protected $_history;    
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magedelight\Giftcard\Model\History $history,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
        $this->_history = $history;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('giftcard_history');
        $this->setDefaultSort('history_id');
        $this->setUseAjax(true);
    }

    
    /**
     * @return array|null
     */
    public function getCode()
    {
        return $this->_coreRegistry->registry('giftacrd_code');
    }
    
    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_history->getCollection()
                ->addFieldToFilter('code_id', $this->getCode()->getCodeId())
                ->setOrder('history_id','DESC');
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'history_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'history_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'Value',
            [
                'header' => __('Value'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'value'
            ]
        );
        $this->addColumn('order', ['header' => __('Order'), 'index' => 'order']);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status']);
        $this->addColumn('comments', ['header' => __('Comments'), 'index' => 'comments']);
        $this->addColumn('action_by', ['header' => __('Action Done By'), 'index' => 'action_by']);
        
        return parent::_prepareColumns();
    }


}

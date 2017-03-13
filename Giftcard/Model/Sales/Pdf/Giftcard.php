<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Giftcard\Model\Sales\Pdf;

class Giftcard extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    
    
    /**
     * @var \Magedelight\Giftcard\Model\Order
     */
    public $giftcardorder;
    
    /**
     * @var \Magedelight\Giftcard\Helper\Data
     */
    public $helper;
    
    /**
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Weee\Helper\Data $_weeeData
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magedelight\Giftcard\Model\Order $giftcardorder,
        \Magedelight\Giftcard\Helper\Data $helper,
        array $data = []
    ){
        $this->giftcardorder = $giftcardorder;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->helper = $helper;
    }
    
    
    public function getTotalsForDisplay()
    {    
        $order = $this->getSource();

        $giftcardTotal = $this->giftcardorder->getGiftcardTotal($order->getOrderId());
        
        if (!$this->helper->isActive()){
            return [];
        }
        
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        
        if ($giftcardTotal){
            $total = [];
            $giftCardCollection = $this->giftcardorder->getCollection()
                    ->addFieldToFilter('order_id', $order->getOrderId());
            foreach ($giftCardCollection as $giftcardOrder) {
                $total[] = array(
                    'label' => __('Discounted from Gift Card (' . $this->helper->secureCode($giftcardOrder->getCode()) . ')'),
                    'amount' => $this->helper->getFormattedPrice(-$giftcardOrder->getBaseDiscount(),false),
                    'font_size' => $fontSize
                );
            }
            return $total;
        }
        return [];
    }
}

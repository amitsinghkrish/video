<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Giftcard\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class ProductPrice extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const cust_name = 'giftcard_price_type';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if(isset($dataSource['data']['items'])){
            $store = $this->storeManager->getStore(
                $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

            $fieldName = self::cust_name;
            $originalName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item){
                if (isset($item[$fieldName])){
                    if($item[$fieldName] == 1){
                        $item[$originalName] = $currency->toCurrency(sprintf("%f", $item['giftcard_balance']));
                    }
                    else{
                        $min =  $currency->toCurrency(sprintf("%f", $item['giftcard_price_min']));
                        $max =  $currency->toCurrency(sprintf("%f", $item['giftcard_price_max']));
                        $item[$originalName] =  '('.$min.' - '.$max.')';
                    }
                }
            }
        }
        return $dataSource;
    }
}

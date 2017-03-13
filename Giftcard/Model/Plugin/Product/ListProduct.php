<?php
namespace Magedelight\Giftcard\Model\Plugin\Product;

use Closure;

class ListProduct
{
    
    public $helper;
    
    public function __construct(
        \Magedelight\Giftcard\Helper\Data $helper
    ){
        $this->helper = $helper;
    }
    
    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param callable $proceed
     * @param array $additional
     * @return \Magento\Sales\Model\Order\Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ){
        $result = $proceed($product);
        if($product->getTypeId() == 'giftcard'){
            return $result = $this->helper->getGiftcardPrice($product);
        }
        return $result;
    }
}
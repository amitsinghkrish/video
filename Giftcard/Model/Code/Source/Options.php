<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Giftcard\Model\Code\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class CodeLayout
 */
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
     /**
     * @var OptionFactory
     */
    protected $optionFactory;
 
    /**
     * @param OptionFactory $optionFactory
     */
    /*public function __construct(OptionFactory $optionFactory)
    {
        $this->optionFactory = $optionFactory;  
        //you can use this if you want to prepare options dynamically  
    }*/
 
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        /* your Attribute options list*/
        $this->_options=[ ['label'=>'Fixed', 'value'=>'1'],
                          ['label'=>'Range', 'value'=>'2']
                         ];
        return $this->_options;
    }
 
    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

}

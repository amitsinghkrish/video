<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Giftcard\Model\Code\Source;

use Magedelight\Giftcard\Model\Code;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CodeLayout
 */
class CodeLayout implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $code;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param BuilderInterface $pageLayoutBuilder
     */
    public function __construct(Code $code)
    {
        $this->code = $code;
    }

    /**
     * Get options
     *
     * @return array
    */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $configOptions = $this->code->getAvailableStatuses();
        $options = [];
        foreach ($configOptions as $key => $value){
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        $this->options = $options;

        return $this->options;
    }
}

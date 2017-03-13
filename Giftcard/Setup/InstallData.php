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

namespace Magedelight\Giftcard\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magedelight\Giftcard\Model\Product\Type\Giftcard;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 

    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        
        
        /* Adding balance attribute */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'giftcard_price_type');
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'giftcard_price_type',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Giftcard Price Type',
                    'input' => 'select',
                    'frontend_class' => 'giftcard_price_type',
                    'source' => 'Magedelight\Giftcard\Model\Code\Source\Options',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Giftcard::TYPE_GIFTCARD_PRODUCT
                ]
        );
        /* Adding balance attribute */
        
        /* Adding balance attribute */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'giftcard_price_min');
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'giftcard_price_min',
                [
                    'type' => 'decimal',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Minimum Price',
                    'input' => 'price',
                    'frontend_class' => 'giftcard_price_min',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Giftcard::TYPE_GIFTCARD_PRODUCT
                ]
        );
        /* Adding balance attribute */
        
        /* Adding balance attribute */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'giftcard_price_max');
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'giftcard_price_max',
                [
                    'type' => 'decimal',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Maximum Price',
                    'input' => 'price',
                    'frontend_class' => 'giftcard_price_max',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Giftcard::TYPE_GIFTCARD_PRODUCT
                ]
        );
        /* Adding balance attribute */
        
       /* Adding balance attribute */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'giftcard_balance');
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'giftcard_balance',
                [
                    'type' => 'decimal',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Giftcard Balance',
                    'input' => 'price',
                    'frontend_class' => 'validate-greater-than-zero',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Giftcard::TYPE_GIFTCARD_PRODUCT
                ]
        );
        /* Adding balance attribute */
        
         /* Adding lifetime attribute */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'giftcard_lifetime');
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'giftcard_lifetime',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Lifetime (days)',
                    'input' => 'text',
                    'frontend_class' => 'validate-number',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => 30,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Giftcard::TYPE_GIFTCARD_PRODUCT
                ]
        );
        /* Adding lifetime attribute */
    }
}

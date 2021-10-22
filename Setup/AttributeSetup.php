<?php
/**
 * Copyright 2021 (c) mediarox UG (haftungsbeschraenkt) (http://www.mediarox.de)
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Catalog\AttributeSetup\Setup;

use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mediarox\EavPropertyMapper\Plugin\Entity\Setup\PropertyMapper;
use Magento\Framework\Setup\ConsoleLogger;

class AttributeSetup extends EavSetup
{
    /**
     * In interaction with the module "mediarox/module-eav-property-mapper" (dependency)
     * it is expected by convention that the attribute properties are used exclusively
     * in their long form ('frontend_input' instead of 'input').
     *
     * Example of 1 item: 'category' => 'category_icon' => ['frontend_input' => 'text', ...]
     *
     */
    public array $attributeData;
    public bool $detectNotAllowedShortForms;

    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        array $attributeData,
        bool $detectNotAllowedShortForms = null
    ) {
        $this->attributeData = $attributeData;
        $this->detectNotAllowedShortForms = $detectNotAllowedShortForms ?: true;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    /**
     * Add or update all attributes.
     */
    public function addUpdateAttributes(): void
    {
        if ($this->cancelDueToNotAllowedAttributeShortForms()) {
            return;
        }
        foreach ($this->attributeData as $type => $attributes) {
            foreach ($attributes as $code => $data) {
                $attributeId = $this->getAttributeId($type, $code);
                $attributeIsInstalled = !empty($attributeId);
    
                if ($attributeIsInstalled) {
                    foreach ($data as $field => $value) {
                        $this->updateAttribute($type, $attributeId, $field, $value);
                    }
                } else {
                    $this->addAttribute($type, $code, $data);
                }
            }
        }
    }

    /**
     * Abort as a result of a violation ?
     */
    protected function cancelDueToNotAllowedAttributeShortForms(): bool
    {
        if (!$this->detectNotAllowedShortForms) {
            return false;
        }
        $notAllowedShortForms = array_values(PropertyMapper::EAV_PROPERTIES_LONG_TO_SHORT);
        foreach ($this->attributeData as $type => $attributes) {
            foreach ($attributes as $code => $data) {
                foreach ($data as $field => $value) {
                    if (in_array($field, $notAllowedShortForms, true)) {
                        $message = 'A not allowed use of shorthand notation \'%1\' was detected. ';
                        $message .= 'Please check all recently added jobs that create or update attributes for this occurrence to proceed.';
                        throw new LocalizedException(__($message, $field));
                        return true;
                    }
                }
            }
        }
        return false;
    }
}

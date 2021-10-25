A Magento 2 module that contains an alternative attributes setup class. 
It includes an optimized create/update mechanism.

It's intended to replace "**mediarox/module-catalog-category-attribute**" in a more generic way. (can be used for categories **and** products)

##### examples

1. force product attribute setting with recurring data patch

```php

use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Catalog\AttributeSetup\Setup\AttributeSetup;
use Catalog\AttributeSetup\Setup\AttributeSetupFactory;

class RecurringData implements InstallDataInterface
{
    protected AttributeSetupFactory $attributeSetup;

    public function __construct(
        AttributeSetupFactory $attributeSetup
    ) {
        $this->attributeSetup = $attributeSetup;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var AttributeSetup $attributeSetup */
        $attributeSetup = $this->attributeSetup->create([
            'setup' => $setup,
            'attributeData' => $this->getAttributes()
        ]);

        $setup->startSetup();
        $attributeSetup->addUpdateAttributes();
        $setup->endSetup();
    }

    public function getAttributes() : array
    {
        return [
            Product::ENTITY => [
                'manufacturer' => [
                    'is_filterable' => 0
                ] 
            ]
        ];
    }
}
```




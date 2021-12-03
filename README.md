### Description

A Magento 2 module/library that contains an alternative attributes setup class.
It is intended to simplify the creation/updating of product and category attributes.

### Installation

```bash
composer require mediarox/module-catalog-attribute-setup
bin/magento setup:upgrade
```

### Usage

In most use cases we "use" the supplied setup class
```php
use Catalog\AttributeSetup\Setup\AttributeSetup;
```
or more specific
```php
use Catalog\AttributeSetup\Setup\AttributeSetupFactory;
```
inside a patch file (Recurring or DataPatch).

Our goal was that each patch that wants to install/update attributes only needs to provide an array of information. 
Everything else is then taken care of by the setup class.

To make the point effectively, take note of the following example.

#### Example 1 - RecurringData patch

Force product attribute setting with [recurring data patch](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/prepare/lifecycle.html#recurring-data-event) (useful for development/not released projects).

```<your_module>/Setup/RecurringData.php```

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
###### Example explanations
1. We always use the "getAttributes()" method internally to provide the attribute information. Feel free to do this differently.
2. RecurringData patches use the "install" method as the main entry point. (In DataPatches, the "apply" method is used).
3. RecurringData patches are executed on every "bin/magento setup:upgrade". Please use a [DataPatch](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/declarative-schema/data-patches.html) if you want to run only once.
4. As usual in Magento, we still use a factory class with which we create new instances. (AttributeSetupFactory)
5. In the "install" method we first create the new instance via the internal method "create" and pass our attribute information right away.
   ```php
   $attributeSetup = $this->attributeSetup->create([
   'setup' => $setup,
   'attributeData' => $this->getAttributes()
   ]);
   ```
6. Then, we run the "addUpdateAttributes" method on the instance to start the create/update process.
   ```php
    $attributeSetup->addUpdateAttributes();
   ```

### Additional notes (yes, important)

#### Attribute information structure

As you can see in the example, the attributes array is grouped into the respective entity. ('catalog_product' or 'catalog_category')

#### Attribute property names
Magento unfortunately **does not use a uniform name for the attribute properties**. 
   In some cases you have to use the short form and in others the long form. 
   As a result, **we have decided** to uniformly push **the long form**. Following this, the module loads module [eav-property-mapper](https://github.com/mediarox/module-eav-property-mapper) as a dependency to ensure that we can/must use the long form across the board. ([All short and long forms](https://github.com/mediarox/module-eav-property-mapper/blob/main/Plugin/Entity/Setup/PropertyMapper.php))  

In summary: **The long form must be used.** If not, the script will abort due to validation.
   





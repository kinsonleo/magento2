<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address
 */
namespace Magento\ImportExport\Model\Import\Entity\Eav\Customer;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested class name
     *
     * @var string
     */
    protected $_testClassName = 'Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address';

    /**
     * Fixture key from fixture
     *
     * @var string
     */
    protected $_fixtureKey = '_fixture/Magento_ImportExport_Customers_Array';

    /**
     * Address entity adapter instance
     *
     * @var \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address
     */
    protected $_entityAdapter;

    /**
     * Important data from address_import_update.csv (postcode is key)
     *
     * @var array
     */
    protected $_updateData = array(
        'address' => array( // address records
            'update' => '19107',  // address with updates
            'new' => '85034',  // new address
            'no_customer' => '33602',  // there is no customer with this primary key (email+website)
            'new_no_address_id' => '32301',  // new address without address id
        ),
        'update' => array( // this data is changed in CSV file
            '19107' => array(
                'firstname' => 'Katy',
                'middlename' => 'T.',
            ),
        ),
        'remove' => array( // this data is not set in CSV file
            '19107' => array(
                'city' => 'Philadelphia',
                'region' => 'Pennsylvania',
            ),
        ),
        'default' => array( // new default billing/shipping addresses
            'billing' => '85034',
            'shipping' => '19107',
        ),
    );

    /**
     * Important data from address_import_delete.csv (postcode is key)
     *
     * @var array
     */
    protected $_deleteData = array(
        'delete' => '19107',  // deleted address
        'not_delete' => '72701',  // not deleted address
    );

    /**
     * Init new instance of address entity adapter
     */
    protected function setUp()
    {
        $this->_entityAdapter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            $this->_testClassName
        );
    }

    /**
     * Test constructor
     *
     * @magentoDataFixture Magento/ImportExport/_files/customer_with_addresses.php
     */
    public function testConstruct()
    {
        // check entity table
        $this->assertAttributeInternalType(
            'string',
            '_entityTable',
            $this->_entityAdapter,
            'Entity table must be a string.'
        );
        $this->assertAttributeNotEmpty('_entityTable', $this->_entityAdapter, 'Entity table must not be empty');

        // check message templates
        $this->assertAttributeInternalType(
            'array',
            '_messageTemplates',
            $this->_entityAdapter,
            'Templates must be an array.'
        );
        $this->assertAttributeNotEmpty('_messageTemplates', $this->_entityAdapter, 'Templates must not be empty');

        // check attributes
        $this->assertAttributeInternalType(
            'array',
            '_attributes',
            $this->_entityAdapter,
            'Attributes must be an array.'
        );
        $this->assertAttributeNotEmpty('_attributes', $this->_entityAdapter, 'Attributes must not be empty');

        // check addresses
        $this->assertAttributeInternalType(
            'array',
            '_addresses',
            $this->_entityAdapter,
            'Addresses must be an array.'
        );
        $this->assertAttributeNotEmpty('_addresses', $this->_entityAdapter, 'Addresses must not be empty');

        // check country regions and regions
        $this->assertAttributeInternalType(
            'array',
            '_countryRegions',
            $this->_entityAdapter,
            'Country regions must be an array.'
        );
        $this->assertAttributeNotEmpty('_countryRegions', $this->_entityAdapter, 'Country regions must not be empty');

        $this->assertAttributeInternalType('array', '_regions', $this->_entityAdapter, 'Regions must be an array.');
        $this->assertAttributeNotEmpty('_regions', $this->_entityAdapter, 'Regions must not be empty');
    }

    /**
     * Test _initAddresses
     *
     * @magentoDataFixture Magento/ImportExport/_files/customer_with_addresses.php
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_initAddresses
     */
    public function testInitAddresses()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // get addressed from fixture
        $customers = $objectManager->get('Magento\Registry')->registry($this->_fixtureKey);
        $correctAddresses = array();
        /** @var $customer \Magento\Customer\Model\Customer */
        foreach ($customers as $customer) {
            $correctAddresses[$customer->getId()] = array();
            /** @var $address \Magento\Customer\Model\Address */
            foreach ($customer->getAddressesCollection() as $address) {
                $correctAddresses[$customer->getId()][] = $address->getId();
            }
        }

        // invoke _initAddresses
        $initAddresses = new \ReflectionMethod($this->_testClassName, '_initAddresses');
        $initAddresses->setAccessible(true);
        $initAddresses->invoke($this->_entityAdapter);

        // check addresses
        $this->assertAttributeInternalType(
            'array',
            '_addresses',
            $this->_entityAdapter,
            'Addresses must be an array.'
        );
        $this->assertAttributeNotEmpty('_addresses', $this->_entityAdapter, 'Addresses must not be empty');

        $addressesReflection = new \ReflectionProperty($this->_testClassName, '_addresses');
        $addressesReflection->setAccessible(true);
        $testAddresses = $addressesReflection->getValue($this->_entityAdapter);

        $correctCustomerIds = array_keys($correctAddresses);
        $testCustomerIds = array_keys($testAddresses);
        sort($correctCustomerIds);
        sort($testCustomerIds);
        $this->assertEquals($correctCustomerIds, $testCustomerIds, 'Incorrect customer IDs in addresses array.');

        foreach ($correctCustomerIds as $customerId) {
            $this->assertInternalType('array', $correctAddresses[$customerId], 'Addresses must be an array.');
            $correctAddressIds = $correctAddresses[$customerId];
            $testAddressIds = $testAddresses[$customerId];
            sort($correctAddressIds);
            sort($testAddressIds);
            $this->assertEquals($correctAddressIds, $testAddressIds, 'Incorrect addresses IDs.');
        }
    }

    /**
     * Test _saveAddressEntity
     *
     * @magentoDataFixture Magento/ImportExport/_files/customer_with_addresses.php
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_saveAddressEntities
     */
    public function testSaveAddressEntities()
    {
        // invoke _saveAddressEntities
        list($customerId, $addressId) = $this->_addTestAddress($this->_entityAdapter);

        // check DB
        $testAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Address'
        );
        $testAddress->load($addressId);
        $this->assertEquals($addressId, $testAddress->getId(), 'Incorrect address ID.');
        $this->assertEquals($customerId, $testAddress->getParentId(), 'Incorrect address customer ID.');
    }

    /**
     * Add new test address for existing customer
     *
     * @param \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address $entityAdapter
     * @return array (customerID, addressID)
     */
    protected function _addTestAddress(\Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address $entityAdapter)
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $customers = $objectManager->get('Magento\Registry')->registry($this->_fixtureKey);
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = reset($customers);
        $customerId = $customer->getId();

        /** @var $addressModel \Magento\Customer\Model\Address */
        $addressModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Address'
        );
        $tableName = $addressModel->getResource()->getEntityTable();
        $addressId = $objectManager->get(
            'Magento\Core\Model\Resource\HelperPool'
        )->get(
            'Magento_ImportExport'
        )->getNextAutoincrement(
            $tableName
        );

        /** @var \Magento\Stdlib\DateTime $dateTime */
        $dateTime = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Stdlib\DateTime');

        $entityData = array(
            'entity_id' => $addressId,
            'entity_type_id' => $addressModel->getEntityTypeId(),
            'parent_id' => $customerId,
            'created_at' => $dateTime->now(),
            'updated_at' => $dateTime->now()
        );

        // invoke _saveAddressEntities
        $saveAddressEntities = new \ReflectionMethod($this->_testClassName, '_saveAddressEntities');
        $saveAddressEntities->setAccessible(true);
        $saveAddressEntities->invoke($entityAdapter, $entityData);

        return array($customerId, $addressId);
    }

    /**
     * Test _saveAddressAttributes
     *
     * @magentoDataFixture Magento/ImportExport/_files/customer_with_addresses.php
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_saveAddressAttributes
     */
    public function testSaveAddressAttributes()
    {
        // get attributes list
        $attributesReflection = new \ReflectionProperty($this->_testClassName, '_attributes');
        $attributesReflection->setAccessible(true);
        $attributes = $attributesReflection->getValue($this->_entityAdapter);

        // get some attribute
        $attributeName = 'city';
        $this->assertArrayHasKey($attributeName, $attributes, 'Key "' . $attributeName . '" should be an attribute.');
        $attributeParams = $attributes[$attributeName];
        $this->assertArrayHasKey('id', $attributeParams, 'Attribute must have an ID.');
        $this->assertArrayHasKey('table', $attributeParams, 'Attribute must have a table.');

        // create new address with attributes
        $data = $this->_addTestAddress($this->_entityAdapter);
        $addressId = $data[1];
        $attributeId = $attributeParams['id'];
        $attributeTable = $attributeParams['table'];
        $attributeValue = 'Test City';

        $attributeArray = array();
        $attributeArray[$attributeTable][$addressId][$attributeId] = $attributeValue;

        // invoke _saveAddressAttributes
        $saveAttributes = new \ReflectionMethod($this->_testClassName, '_saveAddressAttributes');
        $saveAttributes->setAccessible(true);
        $saveAttributes->invoke($this->_entityAdapter, $attributeArray);

        // check DB
        /** @var $testAddress \Magento\Customer\Model\Address */
        $testAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Address'
        );
        $testAddress->load($addressId);
        $this->assertEquals($addressId, $testAddress->getId(), 'Incorrect address ID.');
        $this->assertEquals($attributeValue, $testAddress->getData($attributeName), 'There is no attribute value.');
    }

    /**
     * Test _saveCustomerDefaults
     *
     * @magentoDataFixture Magento/ImportExport/_files/customer_with_addresses.php
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_saveCustomerDefaults
     */
    public function testSaveCustomerDefaults()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // get not default address
        $customers = $objectManager->get('Magento\Registry')->registry($this->_fixtureKey);
        /** @var $notDefaultAddress \Magento\Customer\Model\Address */
        $notDefaultAddress = null;
        /** @var $addressCustomer \Magento\Customer\Model\Customer */
        $addressCustomer = null;
        /** @var $customer \Magento\Customer\Model\Customer */
        foreach ($customers as $customer) {
            /** @var $address \Magento\Customer\Model\Address */
            foreach ($customer->getAddressesCollection() as $address) {
                if (!$customer->getDefaultBillingAddress() && !$customer->getDefaultShippingAddress()) {
                    $notDefaultAddress = $address;
                    $addressCustomer = $customer;
                    break;
                }
                if ($notDefaultAddress) {
                    break;
                }
            }
        }
        $this->assertNotNull($notDefaultAddress, 'Not default address must exists.');
        $this->assertNotNull($addressCustomer, 'Not default address customer must exists.');

        $addressId = $notDefaultAddress->getId();
        $customerId = $addressCustomer->getId();

        // set customer defaults
        $defaults = array();
        foreach (\Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::
                     getDefaultAddressAttributeMapping() as $attributeCode) {
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $attribute = $addressCustomer->getAttribute($attributeCode);
            $attributeTable = $attribute->getBackend()->getTable();
            $attributeId = $attribute->getId();
            $defaults[$attributeTable][$customerId][$attributeId] = $addressId;
        }

        // invoke _saveCustomerDefaults
        $saveDefaults = new \ReflectionMethod($this->_testClassName, '_saveCustomerDefaults');
        $saveDefaults->setAccessible(true);
        $saveDefaults->invoke($this->_entityAdapter, $defaults);

        // check DB
        /** @var $testCustomer \Magento\Customer\Model\Customer */
        $testCustomer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Customer'
        );
        $testCustomer->load($customerId);
        $this->assertEquals($customerId, $testCustomer->getId(), 'Customer must exists.');
        $this->assertNotNull($testCustomer->getDefaultBillingAddress(), 'Default billing address must exists.');
        $this->assertNotNull($testCustomer->getDefaultShippingAddress(), 'Default shipping address must exists.');
        $this->assertEquals(
            $addressId,
            $testCustomer->getDefaultBillingAddress()->getId(),
            'Incorrect default billing address.'
        );
        $this->assertEquals(
            $addressId,
            $testCustomer->getDefaultShippingAddress()->getId(),
            'Incorrect default shipping address.'
        );
    }

    /**
     * Test import data method with add/update behaviour
     *
     * @magentoDataFixture Magento/ImportExport/_files/customers_for_address_import.php
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_importData
     */
    public function testImportDataAddUpdate()
    {
        // set behaviour
        $this->_entityAdapter->setParameters(
            array('behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE)
        );

        // set fixture CSV file
        $sourceFile = __DIR__ . '/../_files/address_import_update.csv';

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $filesystem = $objectManager->create('Magento\App\Filesystem');

        $directoryWrite = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::ROOT_DIR);

        $result = $this->_entityAdapter->setSource(
            \Magento\ImportExport\Model\Import\Adapter::findAdapterFor($sourceFile, $directoryWrite)
        )->isDataValid();
        $this->assertFalse($result, 'Validation result must be false.');

        // import data
        $this->_entityAdapter->importData();

        // form attribute list
        $keyAttribute = 'postcode';
        $requiredAttributes[] = $keyAttribute;
        foreach (array('update', 'remove') as $action) {
            foreach ($this->_updateData[$action] as $attributes) {
                $requiredAttributes = array_merge($requiredAttributes, array_keys($attributes));
            }
        }

        // get addresses
        $addressCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Address\Collection'
        );
        $addressCollection->addAttributeToSelect($requiredAttributes);
        $addresses = array();
        /** @var $address \Magento\Customer\Model\Address */
        foreach ($addressCollection as $address) {
            $addresses[$address->getData($keyAttribute)] = $address;
        }

        // is addresses exists
        $this->assertArrayHasKey($this->_updateData['address']['update'], $addresses, 'Address must exist.');
        $this->assertArrayHasKey($this->_updateData['address']['new'], $addresses, 'Address must exist.');
        $this->assertArrayNotHasKey(
            $this->_updateData['address']['no_customer'],
            $addresses,
            'Address must not exist.'
        );
        $this->assertArrayHasKey(
            $this->_updateData['address']['new_no_address_id'],
            $addresses,
            'Address must exist.'
        );

        // are updated address fields have new values
        $updatedAddressId = $this->_updateData['address']['update'];
        /** @var $updatedAddress \Magento\Customer\Model\Address */
        $updatedAddress = $addresses[$updatedAddressId];
        $updatedData = $this->_updateData['update'][$updatedAddressId];
        foreach ($updatedData as $fieldName => $fieldValue) {
            $this->assertEquals($fieldValue, $updatedAddress->getData($fieldName));
        }

        // are removed data fields have old values
        $removedData = $this->_updateData['remove'][$updatedAddressId];
        foreach ($removedData as $fieldName => $fieldValue) {
            $this->assertEquals($fieldValue, $updatedAddress->getData($fieldName));
        }

        // are default billing/shipping addresses have new value
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Customer'
        );
        $customer->setWebsiteId(0);
        $customer->loadByEmail('BetsyParker@example.com');
        $defaultsData = $this->_updateData['default'];
        $this->assertEquals(
            $defaultsData['billing'],
            $customer->getDefaultBillingAddress()->getData($keyAttribute),
            'Incorrect default billing address'
        );
        $this->assertEquals(
            $defaultsData['shipping'],
            $customer->getDefaultShippingAddress()->getData($keyAttribute),
            'Incorrect default shipping address'
        );
    }

    /**
     * Test import data method with delete behaviour
     *
     * @magentoDataFixture Magento/ImportExport/_files/customers_for_address_import.php
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_importData
     */
    public function testImportDataDelete()
    {
        // set behaviour
        $this->_entityAdapter->setParameters(array('behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE));

        // set fixture CSV file
        $sourceFile = __DIR__ . '/../_files/address_import_delete.csv';

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $filesystem = $objectManager->create('Magento\App\Filesystem');
        $directoryWrite = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::ROOT_DIR);
        $result = $this->_entityAdapter->setSource(
            \Magento\ImportExport\Model\Import\Adapter::findAdapterFor($sourceFile, $directoryWrite)
        )->isDataValid();
        $this->assertTrue($result, 'Validation result must be true.');

        // import data
        $this->_entityAdapter->importData();

        // key attribute
        $keyAttribute = 'postcode';

        // get addresses
        /** @var $addressCollection \Magento\Customer\Model\Resource\Address\Collection */
        $addressCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Address\Collection'
        );
        $addressCollection->addAttributeToSelect($keyAttribute);
        $addresses = array();
        /** @var $address \Magento\Customer\Model\Address */
        foreach ($addressCollection as $address) {
            $addresses[$address->getData($keyAttribute)] = $address;
        }

        // is addresses exists
        $this->assertArrayNotHasKey($this->_deleteData['delete'], $addresses, 'Address must not exist.');
        $this->assertArrayHasKey($this->_deleteData['not_delete'], $addresses, 'Address must exist.');
    }
}

<?php
/**
 * Test for Mage_Index_Model_Indexer
 *
 * We have to implement it in Mage_Catalog module, because Mage_Index module doesn't implement any index processes
 * and also the original Mage_Index_Model_Indexer is not coverable with unit tests in current implementation
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @magentoDbIsolation enabled
 */
class Mage_Catalog_IndexerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Index_Model_Indexer
     */
    protected $_indexer;

    protected function setUp()
    {
        $this->_indexer = Mage::getModel('Mage_Index_Model_Indexer');
    }

    protected function tearDown()
    {
        $this->_indexer = null;
    }

    public function testReindexAll()
    {
        $process = $this->_getProcessModel('catalog_product_price');
        $process->setStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)->save();
        $this->assertEquals(
            Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX,
            $this->_getProcessModel('catalog_product_price')->getStatus()
        );

        $this->_indexer->reindexAll();

        $this->assertEquals(
            Mage_Index_Model_Process::STATUS_PENDING,
            $this->_getProcessModel('catalog_product_price')->getStatus()
        );
    }

    /**
     * @depends testReindexAll
     */
    public function testReindexRequired()
    {
        $process = $this->_getProcessModel('catalog_product_attribute');
        $process->setStatus(Mage_Index_Model_Process::STATUS_RUNNING)->save();
        $process = $this->_getProcessModel('catalog_product_price');
        $process->setStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)->save();
        $this->assertEquals(
            Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX,
            $this->_getProcessModel('catalog_product_price')->getStatus()
        );

        $this->_indexer->reindexRequired();

        $this->assertEquals(
            Mage_Index_Model_Process::STATUS_RUNNING,
            $this->_getProcessModel('catalog_product_attribute')->getStatus()
        );
        $this->assertEquals(
            Mage_Index_Model_Process::STATUS_PENDING,
            $this->_getProcessModel('catalog_product_price')->getStatus()
        );
    }

    /**
     * Load and instantiate index process model
     *
     * We want to load it every time instead of receiving using Mage_Index_Model_Indexer::getProcessByCode()
     * Because that method depends on state of the object, which does not reflect changes in database
     *
     * @param string $typeCode
     * @return Mage_Index_Model_Process
     */
    private function _getProcessModel($typeCode)
    {
        $process = Mage::getModel('Mage_Index_Model_Process');
        $process->load($typeCode, 'indexer_code');
        return $process;
    }
}

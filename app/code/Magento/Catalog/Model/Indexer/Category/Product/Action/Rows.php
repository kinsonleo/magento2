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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

class Rows extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * Limitation by categories
     *
     * @var int[]
     */
    protected $limitationByCategories;

    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @param bool $useTempTable
     * @return $this
     */
    public function execute(array $entityIds = array(), $useTempTable = false)
    {
        $this->limitationByCategories = $entityIds;
        $this->useTempTable = $useTempTable;

        $this->removeEntries();

        $this->reindex();

        return $this;
    }

    /**
     * Return array of all category root IDs + tree root ID
     *
     * @return int[]
     */
    protected function getRootCategoryIds()
    {
        $rootIds = array(\Magento\Catalog\Model\Category::TREE_ROOT_ID);
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->getPathFromCategoryId($store->getRootCategoryId())) {
                $rootIds[] = $store->getRootCategoryId();
            }
        }
        return $rootIds;
    }

    /**
     * Remove index entries before reindexation
     *
     * @return void
     */
    protected function removeEntries()
    {
        $removalCategoryIds = array_diff($this->limitationByCategories, $this->getRootCategoryIds());
        $this->getWriteAdapter()->delete($this->getMainTable(), array('category_id IN (?)' => $removalCategoryIds));
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Core\Model\Store $store
     * @return \Magento\DB\Select
     */
    protected function getNonAnchorCategoriesSelect(\Magento\Core\Model\Store $store)
    {
        $select = parent::getNonAnchorCategoriesSelect($store);
        return $select->where('cc.entity_id IN (?)', $this->limitationByCategories);
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Core\Model\Store $store
     * @return \Magento\DB\Select
     */
    protected function getAnchorCategoriesSelect(\Magento\Core\Model\Store $store)
    {
        $select = parent::getAnchorCategoriesSelect($store);
        return $select->where('cc.entity_id IN (?)', $this->limitationByCategories);
    }

    /**
     * Check whether select ranging is needed
     *
     * @return bool
     */
    protected function isRangingNeeded()
    {
        return false;
    }

    /**
     * Check whether indexation of root category is needed
     *
     * @return bool
     */
    protected function isIndexRootCategoryNeeded()
    {
        return false;
    }
}

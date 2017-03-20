<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet_SolrCategoriesFilter
 * @package    IntegerNet
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\SolrCategoriesFilter\Plugin;

use Magento\Framework\Search\Adapter\Mysql\AggregationFactory as Subject;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class AggregationFactoryPlugin
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var int[]
     */
    private $allowedCategoryIds;

    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {

        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Only accept category buckets on highest level (2)
     *
     * @param Subject $subject
     * @param array $rawAggregation
     * @return array
     */
    public function beforeCreate(Subject $subject, $rawAggregation)
    {
        $categoryIds = array_keys($rawAggregation['category_bucket']);
        $this->allowedCategoryIds = $this->getAllowedCategoryIds($categoryIds);

        $rawAggregation['category_bucket'] = array_filter($rawAggregation['category_bucket'], function ($key) {
            return in_array($key, $this->allowedCategoryIds);
        }, ARRAY_FILTER_USE_KEY);

        return [$rawAggregation];
    }

    /**
     * @param int[] $categoryIds
     * @return int[]
     */
    private function getAllowedCategoryIds($categoryIds)
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();

        $categoryCollection->addIdFilter($categoryIds);
        $categoryCollection->addAttributeToFilter('level', ['eq' => 2]);

        return $categoryCollection->getAllIds();
    }
}
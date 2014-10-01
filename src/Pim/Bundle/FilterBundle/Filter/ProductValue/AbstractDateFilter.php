<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\AbstractDateFilter as OroAbstractDateFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

/**
 * Date filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractDateFilter extends OroAbstractDateFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        /** @var $dateStartValue \DateTime */
        $dateStartValue = $data['date_start'];
        /** @var $dateEndValue \DateTime */
        $dateEndValue = $data['date_end'];
        $type         = $data['type'];

        $this->applyFilterDependingOnType(
            $type,
            $ds,
            $dateStartValue,
            $dateEndValue,
            $this->get(ProductFilterUtility::DATA_NAME_KEY)
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function isValidData($data)
    {
        // Empty operator does not need any value
        if (is_array($data) && isset($data['type']) && FilterType::TYPE_EMPTY === $data['type']) {
            return true;
        }

        return parent::isValidData($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterDependingOnType($type, $ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        switch ($type) {
            case DateRangeFilterType::TYPE_MORE_THAN:
                $this->applyFilterLessMore($ds, $dateStartValue, $fieldName, false);
                break;
            case DateRangeFilterType::TYPE_LESS_THAN:
                $this->applyFilterLessMore($ds, $dateEndValue, $fieldName, true);
                break;
            case DateRangeFilterType::TYPE_NOT_BETWEEN:
                $this->applyFilterNotBetween($ds, $dateStartValue, $dateEndValue, $fieldName);
                break;
            case FilterType::TYPE_EMPTY:
                $this->util->applyFilter(
                    $ds,
                    $this->get(ProductFilterUtility::DATA_NAME_KEY),
                    null,
                    'EMPTY'
                );
                break;
            default:
            case DateRangeFilterType::TYPE_BETWEEN:
                $this->applyFilterBetween($ds, $dateStartValue, $dateEndValue, $fieldName);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterBetween($ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        if ($dateStartValue && $dateEndValue) {
            $this->util->applyFilter(
                $ds,
                $fieldName,
                array($dateStartValue, $dateEndValue),
                'BETWEEN'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterLessMore($ds, $dateValue, $fieldName, $isLess)
    {
        $this->util->applyFilter($ds, $fieldName, $dateValue, $isLess ? '<' : '>');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterNotBetween($ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        if ($dateStartValue && $dateEndValue) {
            $this->util->applyFilter(
                $ds,
                $fieldName,
                array($dateStartValue, $dateEndValue),
                'NOT BETWEEN'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        if (!$this->isValidData($data)) {
            return false;
        }

        if ($data['type'] === FilterType::TYPE_EMPTY) {
            return [
                'date_start' => null,
                'date_end'   => null,
                'type'       => $data['type']
            ];
        }

        return parent::parseData($data);
    }
}

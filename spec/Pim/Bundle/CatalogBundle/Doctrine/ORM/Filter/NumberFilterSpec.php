<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class NumberFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($attrValidatorHelper, ['pim_catalog_number'], ['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_binary_filter_in_the_query($attrValidatorHelper, QueryBuilder $queryBuilder, AttributeInterface $number)
    {
        $attrValidatorHelper->validateLocale($number, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($number, Argument::any())->shouldBeCalled();

        $number->getId()->willReturn(42);
        $number->getCode()->willReturn('number');
        $number->getBackendType()->willReturn('varchar');
        $number->isLocalizable()->willReturn(false);
        $number->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filternumber.attribute = 42 AND filternumber.varchar = 12";

        $queryBuilder->innerJoin('p.values', 'filternumber', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($number, '=', 12);
    }

    function it_adds_empty_filter_in_the_query($attrValidatorHelper, QueryBuilder $queryBuilder, AttributeInterface $number)
    {
        $attrValidatorHelper->validateLocale($number, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($number, Argument::any())->shouldBeCalled();

        $number->getId()->willReturn(42);
        $number->getCode()->willReturn('number');
        $number->getBackendType()->willReturn('varchar');
        $number->isLocalizable()->willReturn(false);
        $number->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filternumber.attribute = 42";

        $queryBuilder->leftJoin('p.values', 'filternumber', 'WITH', $condition)->shouldBeCalled();
        $queryBuilder->andWhere('filternumber.varchar IS NULL')->shouldBeCalled();

        $this->addAttributeFilter($number, 'EMPTY', 12);
    }

    function it_throws_an_exception_if_value_is_not_a_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('number_code');
        $this->shouldThrow(InvalidArgumentException::numericExpected('number_code', 'filter', 'number', gettype('WRONG')))->during('addAttributeFilter', [$attribute, '=', 'WRONG']);
    }
}

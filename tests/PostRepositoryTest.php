<?php

namespace App\Tests;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use PHPUnit\Framework\TestCase;

class PostRepositoryTest extends TestCase
{
    public function testCreateTitleOrContentCriteria(): void
    {
        $postRepository = $this->getMockBuilder(PostRepository::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $query = 'test';
        $criteria = $postRepository->createTitleOrContentCriteria($query);

        $this->assertInstanceOf(Criteria::class, $criteria);

        $expression = $criteria->getWhereExpression();

        $this->assertInstanceOf(CompositeExpression::class, $expression);
        $this->assertEquals(CompositeExpression::TYPE_OR, $expression->getType());

        $expressions = $expression->getExpressionList();
        $this->assertCount(2, $expressions);

        $this->assertInstanceOf(Comparison::class, $expressions[0]);
        $this->assertEquals('title', $expressions[0]->getField());
        $this->assertEquals(Comparison::CONTAINS, $expressions[0]->getOperator());
        $this->assertEquals($query, $expressions[0]->getValue()->getValue());

        $this->assertInstanceOf(Comparison::class, $expressions[1]);
        $this->assertEquals('content', $expressions[1]->getField());
        $this->assertEquals(Comparison::CONTAINS, $expressions[1]->getOperator());
        $this->assertEquals($query, $expressions[1]->getValue()->getValue());
    }
}

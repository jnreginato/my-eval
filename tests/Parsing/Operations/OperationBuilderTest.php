<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * Class OperationBuilderTest
 */
class OperationBuilderTest extends TestCase
{
    /**
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     */
    public function testCanProcessesAddition(): void
    {
        $builder = new OperationBuilder();
        static::assertEquals(new IntegerNode(3), $builder->addition(new IntegerNode(1), new IntegerNode(2)));
    }

    /**
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     */
    public function testCanProcessesSubtraction(): void
    {
        $builder = new OperationBuilder();
        static::assertEquals(new IntegerNode(1), $builder->subtraction(new IntegerNode(2), new IntegerNode(1)));
    }

    /**
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     */
    public function testCanProcessesUnaryMinus(): void
    {
        $builder = new OperationBuilder();
        static::assertEquals(new IntegerNode(-1), $builder->unaryMinus(new IntegerNode(1)));
    }

    /**
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     */
    public function testCanProcessesMultiplication(): void
    {
        $builder = new OperationBuilder();
        static::assertEquals(new IntegerNode(10), $builder->multiplication(new IntegerNode(2), new IntegerNode(5)));
    }

    /**
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     */
    public function testCanProcessesDivision(): void
    {
        $builder = new OperationBuilder();
        static::assertEquals(new RationalNode(2, 1), $builder->division(new IntegerNode(10), new IntegerNode(5)));
    }

    /**
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     * @throws UnexpectedOperatorException
     * @throws ExponentialException
     */
    public function testCanProcessesExponentiation(): void
    {
        $builder = new OperationBuilder();
        static::assertEquals(new IntegerNode(8), $builder->exponentiation(new IntegerNode(2), new IntegerNode(3)));
    }

    /**
     * @throws UnknownOperatorException
     */
    public function testCanProcessesRelation(): void
    {
        $builder = new OperationBuilder();
        static::assertEquals(
            new InfixExpressionNode('>=', new VariableNode('x'), new IntegerNode(1)),
            $builder->relation(new VariableNode('x'), new IntegerNode(1), '>=')
        );

        static::assertEquals(
            new BooleanNode('true'),
            $builder->relation(new IntegerNode(2), new IntegerNode(1), '>=')
        );

        static::assertEquals(
            new BooleanNode('false'),
            $builder->relation(new IntegerNode(2), new IntegerNode(1), '<=')
        );
    }

    /**
     * @throws UnknownOperatorException
     */
    public function testCanProcessesConjunction(): void
    {
        $builder = new OperationBuilder();

        static::assertEquals(
            new InfixExpressionNode(
                '&&',
                new InfixExpressionNode('>=', new VariableNode('x'), new IntegerNode(1)),
                new InfixExpressionNode('<=', new VariableNode('y'), new IntegerNode(2))
            ),
            $builder->conjunction(
                new InfixExpressionNode('>=', new VariableNode('x'), new IntegerNode(1)),
                new InfixExpressionNode('<=', new VariableNode('y'), new IntegerNode(2))
            )
        );

        static::assertEquals(
            new BooleanNode('true'),
            $builder->conjunction(
                new InfixExpressionNode('>=', new IntegerNode(2), new IntegerNode(1)),
                new InfixExpressionNode('<=', new IntegerNode(2), new IntegerNode(2))
            )
        );
    }

    /**
     * @throws UnknownOperatorException
     */
    public function testCanProcessesDisjunction(): void
    {
        $builder = new OperationBuilder();

        static::assertEquals(
            new InfixExpressionNode(
                '||',
                new InfixExpressionNode('<>', new VariableNode('x'), new IntegerNode(1)),
                new InfixExpressionNode('<=', new VariableNode('y'), new IntegerNode(2))
            ),
            $builder->disjunction(
                new InfixExpressionNode('<>', new VariableNode('x'), new IntegerNode(1)),
                new InfixExpressionNode('<=', new VariableNode('y'), new IntegerNode(2))
            )
        );

        static::assertEquals(
            new BooleanNode('true'),
            $builder->disjunction(
                new InfixExpressionNode('>=', new IntegerNode(2), new IntegerNode(1)),
                new InfixExpressionNode('<=', new IntegerNode(3), new IntegerNode(2))
            )
        );
    }

    /**
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanProcessesCondition(): void
    {
        $builder = new OperationBuilder();
        static::assertEquals(
            new TernaryExpressionNode(
                new InfixExpressionNode('>=', new VariableNode('x'), new IntegerNode(1)),
                new IntegerNode(2),
                new IntegerNode(3)
            ),
            $builder->condition(
                new InfixExpressionNode('>=', new VariableNode('x'), new IntegerNode(1)),
                new IntegerNode(2),
                new IntegerNode(3)
            )
        );

        static::assertEquals(
            new IntegerNode(1),
            $builder->condition(
                new InfixExpressionNode('>=', new IntegerNode(6), new IntegerNode(1)),
                new IntegerNode(1),
                new IntegerNode(2)
            )
        );
    }

    /**
     * @throws UnknownOperatorException
     * @throws ExponentialException
     * @throws DivisionByZeroException
     * @throws NullOperandException
     * @throws UnexpectedOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanSimplify(): void
    {
        $builder = new OperationBuilder();

        static::assertEquals(
            new IntegerNode(3),
            $builder->simplify(new InfixExpressionNode('+', new IntegerNode(2), new IntegerNode(1)))
        );

        static::assertEquals(
            new IntegerNode(1),
            $builder->simplify(new InfixExpressionNode('-', new IntegerNode(2), new IntegerNode(1)))
        );

        static::assertEquals(
            new IntegerNode(10),
            $builder->simplify(new InfixExpressionNode('*', new IntegerNode(2), new IntegerNode(5)))
        );

        static::assertEquals(
            new RationalNode(10, 1),
            $builder->simplify(new InfixExpressionNode('/', new IntegerNode(100), new IntegerNode(10)))
        );

        static::assertEquals(
            new IntegerNode(8),
            $builder->simplify(new InfixExpressionNode('^', new IntegerNode(2), new IntegerNode(3)))
        );

        static::assertEquals(
            new IntegerNode(1),
            $builder->simplify(
                new TernaryExpressionNode(new InfixExpressionNode('>', 3, 2), new IntegerNode(1), new IntegerNode(0))
            )
        );

        static::assertEquals(
            new BooleanNode('true'),
            $builder->simplify(new InfixExpressionNode('=', new IntegerNode(2), new IntegerNode(2)))
        );

        static::assertEquals(
            new BooleanNode('false'),
            $builder->simplify(new InfixExpressionNode('&&', new BooleanNode('true'), new BooleanNode('false')))
        );

        static::assertEquals(
            new BooleanNode('true'),
            $builder->simplify(new InfixExpressionNode('||', new BooleanNode('true'), new BooleanNode('false')))
        );
    }

    /**
     * @throws ExponentialException
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     * @throws UnexpectedOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanEmitExceptionOnSimplify(): void
    {
        $builder = new OperationBuilder();

        $this->expectException(NullOperandException::class);
        $builder->simplify(new InfixExpressionNode('+', null, new IntegerNode(1)));
    }

    /**
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws SyntaxErrorException
     */
    public function testCanEmitExceptionOnSimplify2(): void
    {
        $builder = new OperationBuilder();

        $this->expectException(NullOperandException::class);
        $builder->simplify(new InfixExpressionNode('+', new IntegerNode(1), null));
    }
}

<?php

declare(strict_types=1);

namespace MyEval\Parsing\Operations;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operator\AbstractExpressionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;
use MyEval\Parsing\Operations\Conditional\ConditionOperation;
use MyEval\Parsing\Operations\Logical\ConjunctionOperation;
use MyEval\Parsing\Operations\Logical\DisjunctionOperation;
use MyEval\Parsing\Operations\Math\AdditionOperation;
use MyEval\Parsing\Operations\Math\DivisionOperation;
use MyEval\Parsing\Operations\Math\ExponentiationOperation;
use MyEval\Parsing\Operations\Math\MultiplicationOperation;
use MyEval\Parsing\Operations\Math\SubtractionOperation;
use MyEval\Parsing\Operations\Relational\RelationalOperation;

/**
 * Helper class for creating ExpressionNodes.
 *
 * Wrapper class, setting up factories for creating InfixExpressionNodes or TernaryExpressionNodes of various types
 * (one for each operator). These factories take case of basic simplification.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\Parsing\Operations\OperationBuilder;
 *
 * $builder = new OperationBuilder();
 * // Create AST for 'x/y + x*y'
 * $node = $builder->addition(
 *      $builder->division(new VariableNode('x'), new VariableNode('y')),
 *      $builder->multiplication(new VariableNode('x'), new VariableNode('y'))
 * );
 * ~~~
 */
class OperationBuilder
{
    private AdditionOperation $additionOperation;

    private SubtractionOperation $subtractionOperation;

    private MultiplicationOperation $multiplicationOperation;

    private DivisionOperation $divisionOperation;

    private ExponentiationOperation $exponentiationOperation;

    private RelationalOperation $relationalOperation;

    private ConjunctionOperation $conjunctionOperation;

    private DisjunctionOperation $disjunctionOperation;

    private ConditionOperation $conditionalOperation;

    public function __construct()
    {
        // Mathematical
        $this->additionOperation       = new AdditionOperation();
        $this->subtractionOperation    = new SubtractionOperation();
        $this->multiplicationOperation = new MultiplicationOperation();
        $this->divisionOperation       = new DivisionOperation();
        $this->exponentiationOperation = new ExponentiationOperation();

        // Relational
        $this->relationalOperation = new RelationalOperation();

        // Logical
        $this->conjunctionOperation = new ConjunctionOperation();
        $this->disjunctionOperation = new DisjunctionOperation();

        // Conditional
        $this->conditionalOperation = new ConditionOperation();
    }

    /**
     * Create an addition node representing '$leftOperand + $rightOperand'.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function addition(Node $leftOperand, Node $rightOperand): Node
    {
        return $this->additionOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * Create a subtraction node representing '$leftOperand - $rightOperand'.
     *
     * @param Node      $leftOperand
     * @param Node|null $rightOperand
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function subtraction(Node $leftOperand, ?Node $rightOperand): Node
    {
        return $this->subtractionOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * Create a unary minus node representing '-$operand'.
     *
     * @param Node $operand
     *
     * @return InfixExpressionNode
     * @throws UnknownOperatorException
     * @throws DivisionByZeroException
     */
    public function unaryMinus(Node $operand): Node
    {
        return $this->subtractionOperation->createUnaryMinusNode($operand);
    }

    /**
     * Create a multiplication node representing '$leftOperand * $rightOperand'.
     *
     * @param mixed $leftOperand
     * @param mixed $rightOperand
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function multiplication(Node $leftOperand, Node $rightOperand): Node
    {
        return $this->multiplicationOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * Create a division node representing '$leftOperand / $rightOperand'.
     *
     * @param mixed $leftOperand
     * @param mixed $rightOperand
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     */
    public function division(Node $leftOperand, Node $rightOperand): Node
    {
        return $this->divisionOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * Create an exponentiation node representing '$leftOperand ^ $rightOperand'.
     *
     * @param mixed $leftOperand
     * @param mixed $rightOperand
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws ExponentialException
     */
    public function exponentiation(Node $leftOperand, Node $rightOperand): Node
    {
        return $this->exponentiationOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * Create a relation node representing '$leftOperand {operator} $rightOperand',
     * where operator: '=', '>', '<', '<>', '>=' or '<='.
     *
     * @param mixed  $leftOperand
     * @param mixed  $rightOperand
     * @param string $operator
     *
     * @return Node
     * @throws UnknownOperatorException
     */
    public function relation(Node $leftOperand, Node $rightOperand, string $operator): Node
    {
        return $this->relationalOperation->makeNode($leftOperand, $rightOperand, $operator);
    }

    /**
     * Create a logical node representing '$leftOperand AND $rightOperand'.
     *
     * @param mixed $leftOperand
     * @param mixed $rightOperand
     *
     * @return Node
     * @throws UnknownOperatorException
     */
    public function conjunction(Node $leftOperand, Node $rightOperand): Node
    {
        return $this->conjunctionOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * Create a logical node representing '$leftOperand OR $rightOperand'.
     *
     * @param mixed $leftOperand
     * @param mixed $rightOperand
     *
     * @return Node
     * @throws UnknownOperatorException
     */
    public function disjunction(Node $leftOperand, Node $rightOperand): Node
    {
        return $this->disjunctionOperation->makeNode($leftOperand, $rightOperand);
    }

    /**
     * Create a condition node representing 'IF (condition) THEN { then } ELSE { else }' operation.
     *
     * @param Node  $condition
     * @param mixed $then
     * @param mixed $else
     *
     * @return Node
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function condition(Node $condition, Node $then, Node $else): Node
    {
        return $this->conditionalOperation->makeNode($condition, $then, $else);
    }

    /**
     * Simplify the given ExpressionNode, using the appropriate factory.
     *
     * @param AbstractExpressionNode $node
     *
     * @return Node Simplified version of the input
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     * @throws SyntaxErrorException
     */
    public function simplify(AbstractExpressionNode $node): Node
    {
        $operator  = $node->operator;
        $left      = $node->getLeft();
        $right     = $node->getRight();
        $condition = $node instanceof TernaryExpressionNode ? $node->getCondition() : '';

        if ($left === null || ($right === null && $operator !== '-')) {
            throw new NullOperandException();
        }

        return match ($operator) {
            '+'                             => $this->addition($left, $right),
            '-'                             => $this->subtraction($left, $right),
            '*'                             => $this->multiplication($left, $right),
            '/'                             => $this->division($left, $right),
            '^'                             => $this->exponentiation($left, $right),
            '=', '<', '>', '<>', '<=', '>=' => $this->relation($left, $right, $operator),
            '&&', 'AND'                     => $this->conjunction($left, $right),
            '||', 'OR'                      => $this->disjunction($left, $right),
            'if', 'IF'                      => $this->condition($condition, $left, $right),
            default                         => $node,
        };
    }
}

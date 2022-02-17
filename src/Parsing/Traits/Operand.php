<?php

declare(strict_types=1);

namespace MyEval\Parsing\Traits;

use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\OperandNode;

use function get_class;

/**
 * Trait for upgrading numbers (integers and floats) to NumberNode, making it possible to call the Node constructors
 * directly with numbers, making the code cleaner.
 */
trait Operand
{
    /**
     * Simplify expression node when operands are instances of the same OperandNode class.
     *
     * @param Node $leftOperand
     * @param Node $rightOperand
     *
     * @return bool
     */
    public function isSameOperandTerms(Node $leftOperand, Node $rightOperand): bool
    {
        return ($leftOperand instanceof OperandNode && $rightOperand instanceof OperandNode) &&
            (get_class($leftOperand) === get_class($rightOperand));
    }

    /**
     * Process relation of OperandNodes.
     *
     * @param Node   $leftOperand
     * @param Node   $rightOperand
     * @param string $operator
     *
     * @return bool
     * @throws UnknownOperatorException
     */
    public function processRelation(Node $leftOperand, Node $rightOperand, string $operator): bool
    {
        return match ($operator) {
            '='     => $leftOperand->value == $rightOperand->value,
            '>'     => $leftOperand->value > $rightOperand->value,
            '<'     => $leftOperand->value < $rightOperand->value,
            '<>'    => $leftOperand->value != $rightOperand->value,
            '>='    => $leftOperand->value >= $rightOperand->value,
            '<='    => $leftOperand->value <= $rightOperand->value,
            default => throw new UnknownOperatorException($operator),
        };
    }
}

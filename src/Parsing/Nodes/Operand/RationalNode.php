<?php

declare(strict_types=1);

namespace MyEval\Parsing\Nodes\Operand;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Parsing\Nodes\Node;
use MyEval\Solving\Visitor;

use function get_class;

/**
 * AST node representing a rational operand number.
 */
class RationalNode extends NumericNode
{
    /**
     * @param float $value The value of the represented number.
     */
    public readonly float $value;

    /**
     * @param int  $p The numerator of the represented number.
     * @param int  $q The denominator of the represented number.
     * @param bool $normalize
     *
     * @throws DivisionByZeroException
     */
    public function __construct(public int $p, public int $q, bool $normalize = true)
    {
        if ($q === 0) {
            throw new DivisionByZeroException();
        }

        if ($normalize) {
            $this->normalize();
        }

        $this->value = (float)((1.0 * $this->p) / $this->q);
    }

    /**
     * Single function in the Visitable interface.
     *
     * Calling visitRationalNode() function on a Visitor class.
     *
     * ## Example:
     * - evaluators: StdMathEvaluator, RationalEvaluator, ComplexEvaluator, Differentiator, LogicEvaluator or
     * - printers: ASCIIPrinter, LaTeXPrinter, TreePrinter.
     *
     * @param Visitor $visitor
     *
     * @return mixed
     */
    public function accept(Visitor $visitor): mixed
    {
        return $visitor->visitRationalNode($this);
    }

    /**
     * Helper function, comparing two ASTs.
     *
     * Useful for testing and also for some AST transformers.
     *
     * @param OperandNode $other Compare to this tree.
     *
     * @return bool
     */
    public function compareTo(Node $other): bool
    {
        return match (get_class($other)) {
            IntegerNode::class => $this->getDenominator() === 1 && $this->getNumerator() === $other->value,
            static::class      =>
                $this->getNumerator() === $other->getNumerator() &&
                $this->getDenominator() === $other->getDenominator(),
            default            => parent::compareTo($other),
        };
    }

    /**
     * Apply normalization to the rational number.
     *
     * @return void
     */
    private function normalize(): void
    {
        $a = $this->p;
        $b = $this->q;

        while ($b !== 0) {
            $m = $a % $b;
            $a = $b;
            $b = $m;
        }

        $gcd     = $a;
        $this->p /= $gcd;
        $this->q /= $gcd;

        if ($this->q < 0) {
            $this->q = -$this->q;
            $this->p = -$this->p;
        }
    }

    /**
     * Return the numerator of the rational number.
     *
     * @return int
     */
    public function getNumerator(): int
    {
        return $this->p;
    }

    /**
     * Return the denominator of the rational number.
     *
     * @return int
     */
    public function getDenominator(): int
    {
        return $this->q;
    }
}

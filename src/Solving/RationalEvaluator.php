<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnknownConstantException;
use MyEval\Exceptions\UnknownFunctionException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Exceptions\UnknownVariableException;
use MyEval\Extensions\Math;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use UnexpectedValueException;

use function array_key_exists;
use function count;

/**
 * Evalutate a parsed reational mathematical expression.
 *
 * Implementation of a Visitor, transforming an AST into a rational number, giving the *value* of the expression
 * represented by the AST.
 *
 * The class implements evaluation of all arithmetic operators as well as every elementary function and predefined
 * constant recognized by Lexer and StdMathLexer.
 *
 * ## Example:
 *
 * ~~~{.php}
 * use MyEval\RationalMathEval;
 *
 * $evaluator = new RationalMathEval();
 * $result = $evaluator->evaluate('exp(2x)+xy', [ 'x' => '1/2', 'y' => -1 ]);  // Evaluate $result using x=1/2, y=-1.
 * Note that rational variable values should be specified as a string.
 * ~~~
 *
 * or more complex use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\StdMathLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\RationalEvaluator;
 *
 * // Tokenize
 * $lexer = new StdMathLexer();
 * $tokens = $lexer->tokenize('exp(2x)+xy');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Evaluate
 * $evaluator = new RationalEvaluator([ 'x' => '1/2', 'y' => -1 ]);
 * $value = $ast->accept($evaluator);
 * ~~~
 */
class RationalEvaluator implements Visitor
{
    private const DEFAULT_ERROR_MESSAGE = 'Expecting rational number';
    /**
     * @var array $variables Key/value pair holding current values of the variables used for evaluating.
     */
    private array $variables;

    /**
     * @var array $sieve Private cache for prime sieve.
     */
    private static array $sieve = [];

    /**
     * Create an StdMathEvaluator with given variable values.
     *
     * @param mixed $variables Key-value array of variables with corresponding values.
     *
     * @throws DivisionByZeroException
     */
    public function __construct(array $variables = [])
    {
        $this->variables = [];
        foreach ($variables as $var => $value) {
            if ($value instanceof RationalNode) {
                $this->variables[$var] = $value;
            } elseif ($this->isInteger($value)) {
                $this->variables[$var] = new RationalNode((int)$value, 1);
            } else {
                $this->variables[$var] = $this->parseRational($value);
            }
        }
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isInteger($value): bool
    {
        return (bool)preg_match('~^\d+$~', (string)$value);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isSignedInteger($value): bool
    {
        return (bool)preg_match('~^\-?\d+$~', (string)$value);
    }

    /**
     * @param $value
     *
     * @return RationalNode
     * @throws DivisionByZeroException
     */
    public function parseRational($value): RationalNode
    {
        $data = $value;

        $numbers = explode('/', (string)$data);
        if (count($numbers) === 1) {
            $p = $this->isSignedInteger($numbers[0]) ? (int)$numbers[0] : NAN;
            $q = 1;
        } elseif (count($numbers) !== 2) {
            $p = NAN;
            $q = NAN;
        } else {
            $p = $this->isSignedInteger($numbers[0]) ? (int)$numbers[0] : NAN;
            $q = $this->isInteger($numbers[1]) ? (int)$numbers[1] : NAN;
        }

        if (is_nan($p) || is_nan($q)) {
            throw new UnexpectedValueException(self::DEFAULT_ERROR_MESSAGE);
        }

        return new RationalNode($p, $q);
    }

    /**
     * Evaluate an IntegerNode.
     *
     * @param IntegerNode $node AST to be evaluated.
     *
     * @return RationalNode
     * @throws DivisionByZeroException
     */
    public function visitIntegerNode(IntegerNode $node): RationalNode
    {
        return new RationalNode($node->value, 1);
    }

    /**
     * Evaluate an RationalNode.
     *
     * @param RationalNode $node AST to be evaluated.
     **/
    public function visitRationalNode(RationalNode $node): RationalNode
    {
        return $node;
    }

    /**
     * Evaluate a NumberNode.
     *
     * @param FloatNode $node AST to be evaluated.
     *
     * @return void
     */
    public function visitNumberNode(FloatNode $node): void
    {
        throw new UnexpectedValueException(self::DEFAULT_ERROR_MESSAGE);
    }

    /**
     * Evaluate a BooleanNode.
     *
     * @return void
     */
    public function visitBooleanNode(): void
    {
        throw new UnexpectedValueException(self::DEFAULT_ERROR_MESSAGE);
    }

    /**
     * Evaluate a VariableNode.
     *
     * Returns the current value of a VariableNode, as defined by the constructor.
     *
     * @param VariableNode $node AST to be evaluated.
     *
     * @return RationalNode
     * @throws UnknownVariableException If the variable respresented by the is *not* set.
     */
    public function visitVariableNode(VariableNode $node): RationalNode
    {
        $name = $node->value;

        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }

        throw new UnknownVariableException($name);
    }

    /**
     * Evaluate a ConstantNode.
     *
     * Returns the value of a ConstantNode recognized by StdMathLexer.
     *
     * @param ConstantNode $node AST to be evaluated.
     *
     * @return void
     * @throws UnknownConstantException if the variable respresented by the ConstantNode is *not* recognized.
     */
    public function visitConstantNode(ConstantNode $node): void
    {
        throw match ($node->value) {
            'pi', 'e', 'i', 'NAN', 'INF' => new UnexpectedValueException(self::DEFAULT_ERROR_MESSAGE),
            default                      => new UnknownConstantException($node->value),
        };
    }

    /**
     * Evaluate an InfixExpressionNode.
     *
     * Computes the value of an InfixExpressionNode `x op y` where `op` is one of `+`, `-`, `*`, `/` or `^`.
     *
     * @param InfixExpressionNode $node AST to be evaluated.
     *
     * @throws UnknownOperatorException if the operator is something other than `+`, `-`, `*`, `/` or `^`.
     * @throws DivisionByZeroException
     * @throws NullOperandException
     */
    public function visitInfixExpressionNode(InfixExpressionNode $node)
    {
        $left     = $node->getLeft();
        $operator = $node->operator;
        $right    = $node->getRight();

        if ($left === null || ($right === null && $operator !== '-')) {
            throw new NullOperandException();
        }

        /** @var IntegerNode|RationalNode $a */
        $a = $left->accept($this);

        /** @var IntegerNode|RationalNode $b */
        $b = $right?->accept($this);

        // Perform the right operation based on the operator
        switch ($operator) {
            case '+':
                $p = $a->getNumerator() * $b->getDenominator() + $a->getDenominator() * $b->getNumerator();
                $q = $a->getDenominator() * $b->getDenominator();

                return new RationalNode($p, $q);
            case '-':
                if ($b === null) {
                    return new RationalNode(-$a->getNumerator(), $a->getDenominator());
                }
                $p = $a->getNumerator() * $b->getDenominator() - $a->getDenominator() * $b->getNumerator();
                $q = $a->getDenominator() * $b->getDenominator();

                return new RationalNode($p, $q);
            case '*':
                $p = $a->getNumerator() * $b->getNumerator();
                $q = $a->getDenominator() * $b->getDenominator();

                return new RationalNode($p, $q);
            case '/':
                if ($b->getNumerator() === 0) {
                    throw new DivisionByZeroException();
                }

                $p = $a->getNumerator() * $b->getDenominator();
                $q = $a->getDenominator() * $b->getNumerator();

                return new RationalNode($p, $q);
            case '^':
                return $this->rpow($a, $b);
            default:
                throw new UnknownOperatorException($operator);
        }
    }

    /**
     * Evaluate a TernaryNode.
     *
     * @return void
     * @throws SyntaxErrorException
     */
    public function visitTernaryNode(): void
    {
        throw new SyntaxErrorException();
    }

    /**
     * Evaluate a FunctionNode.
     *
     * Computes the value of a FunctionNode `f(x)`, where f is an elementary function recognized by StdMathLexer and
     *
     * @param FunctionNode $node AST to be evaluated
     *
     * @return RationalNode
     * @throws UnknownFunctionException if the function respresented by the FunctionNode is *not* recognized.
     * @throws DivisionByZeroException
     * @throws NullOperandException
     */
    public function visitFunctionNode(FunctionNode $node): RationalNode
    {
        if ($node->operand === null) {
            throw new NullOperandException();
        }

        $inner = $node->operand->accept($this);

        switch ($node->operator) {
            // Trigonometric functions
            case 'sin':
            case 'cos':
            case 'tan':
            case 'cot':
            case 'arcsin':
            case 'arccos':
            case 'arctan':
            case 'arccot':
            case 'exp':
            case 'log':
            case 'ln':
            case 'lg':
            case 'sinh':
            case 'cosh':
            case 'tanh':
            case 'coth':
            case 'arsinh':
            case 'arcosh':
            case 'artanh':
            case 'arcoth':
                throw new UnexpectedValueException(self::DEFAULT_ERROR_MESSAGE);

            case 'abs':
                return new RationalNode(abs($inner->getNumerator()), $inner->getDenominator());

            case 'sgn':
                if ($inner->getNumerator() >= 0) {
                    return new RationalNode(1, 0);
                }
                return new RationalNode(-1, 0);

            // Powers
            case 'sqrt':
                return $this->rpow($inner, new RationalNode(1, 2));

            case '!':
                if ($inner->getDenominator() === 1 && $inner->getNumerator() >= 0) {
                    return new RationalNode(Math::factorial($inner->getNumerator()), 1);
                }
                throw new UnexpectedValueException('Expecting positive integer (factorial)');

            case '!!':
                if ($inner->getDenominator() === 1 && $inner->getNumerator() >= 0) {
                    return new RationalNode(Math::semiFactorial($inner->getNumerator()), 1);
                }
                throw new UnexpectedValueException('Expecting positive integer (factorial)');

            default:
                throw new UnknownFunctionException($node->operator);
        }
    }

    /**
     * Integer factorization
     *
     * Computes an integer factorization of $n using trial division and a cached sieve of computed primes.
     *
     * @param $n
     *
     * @return array
     */
    public static function ifactor($n): array
    {

        // max_n = 2^31-1 = 2147483647
        $d       = 2;
        $factors = [];
        $dmax    = floor(sqrt($n));

        self::$sieve = array_pad(self::$sieve, (int)$dmax, 1);

        do {
            $r = false;
            while ($n % $d === 0) {
                if (array_key_exists($d, $factors)) {
                    $factors[$d]++;
                } else {
                    $factors[$d] = 1;
                }

                $n /= $d;
                $r = true;
            }
            if ($r) {
                $dmax = floor(sqrt($n));
            }
            if ($n > 1) {
                for ($i = $d; $i <= $dmax; $i += $d) {
                    self::$sieve[$i] = 0;
                }
                do {
                    $d++;
                } while ($d < $dmax && self::$sieve[$d] !== 1);

                if ($d > $dmax) {
                    if (array_key_exists($n, $factors)) {
                        $factors[$n]++;
                    } else {
                        $factors[$n] = 1;
                    }
                }
            }
        } while ($n > 1 && $d <= $dmax);

        return $factors;
    }

    /**
     * Compute a power free integer factorization: n = pq^d, where p is d-power free.
     *
     * The function returns an array:
     * [
     *    'square' => q,
     *    'nonSquare' => p
     * ]
     *
     * @param int $n input
     * @param int $d
     *
     * @return array
     */
    public static function powerFreeFactorization(int $n, int $d): array
    {
        $factors = self::ifactor($n);

        $square    = 1;
        $nonSquare = 1;

        foreach ($factors as $prime => $exponent) {
            $remainder = $exponent % $d;

            if ($remainder !== 0) {
                $reducedExponent = ($exponent - $remainder) / $d;
                $nonSquare       *= $prime;
            } else {
                $reducedExponent = $exponent / $d;
            }
            $square *= $prime ** $reducedExponent;
        }

        return ['square' => $square, 'nonSquare' => $nonSquare];
    }

    /**
     * @param $a
     * @param $b
     *
     * @return RationalNode
     * @throws DivisionByZeroException
     */
    private function rpow($a, $b)
    {
        if ($b->getDenominator() === 1) {
            $n = $b->getNumerator();
            if ($n >= 0) {
                return new RationalNode($a->getNumerator() ** $n, $a->getDenominator() ** $n);
            }

            return new RationalNode($a->getDenominator() ** -$n, $a->getNumerator() ** -$n);
        }
        if ($a->getNumerator() < 0) {
            throw new UnexpectedValueException(self::DEFAULT_ERROR_MESSAGE);
        }

        $p = $a->getNumerator();
        $q = $a->getDenominator();

        $alpha = $b->getNumerator();
        $beta  = $b->getDenominator();

        if ($alpha < 0) {
            $temp  = $p;
            $p     = $q;
            $q     = $temp;
            $alpha = -$alpha;
        }

        $pp = $p ** $alpha;
        $qq = $q ** $alpha;

        $ppFactors = self::powerFreeFactorization($pp, $beta);
        $qqFactors = self::powerFreeFactorization($qq, $beta);

        if ($ppFactors['nonSquare'] === 1 && $qqFactors['nonSquare'] === 1) {
            return new RationalNode($ppFactors['square'], $qqFactors['square']);
        }

        throw new UnexpectedValueException('Expecting rational number');
    }
}

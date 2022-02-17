<?php

declare(strict_types=1);

namespace MyEval;

use MyEval\Lexing\StdMathLexer;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Parser;
use MyEval\Solving\StdMathEvaluator;

/**
 * Convenience class for using the library.
 *
 * StdMathEval is a wrapper for the StdMathLexer, Parser and StdMathEvaluator classes, and if you do not require any
 * tweaking, this is the most straightforward way to use the MyEval library.
 *
 * ## Example usage:
 *
 * ~~~{.php}
 * use MyEval\StdMathEval;
 *
 * $evaluator = new StdMathEval();
 * $value = $evaluator->evaluate('2x + 2y^2/sin(x)', [ 'x' => 'pi/3', 'y' => 2.5 ]);
 * ~~~
 *
 * however, if you require a more complex calc, use:
 *
 * ~~~{.php}
 * use MyEval\Lexing\StdMathLexer;
 * use MyEval\Parsing\Parser;
 * use MyEval\Solving\Differentiator;
 * use MyEval\Solving\StdMathEvaluator;
 *
 * // Tokenize
 * $lexer = new StdMathLexer();
 * $tokens = $lexer->tokenize('2x + 2y^2/sin(x)');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Do whatever you want with the parsed expression, for example evaluate it.
 * $evaluator = new StdMathEvaluator([ 'x' => pi/3, 'y' => 2.5 ]);
 * $value = $ast->accept($evaluator);
 *
 * // or differentiate it:
 * $d_dx = new Differentiator('x');
 * $derivative = $ast->accept($d_dx);
 * $valueOfDerivative = $derivative->accept($evaluator);
 * ~~~
 */
class StdMathEval extends AbstractEvaluator
{
    /**
     * @param bool $allowImplicitMultiplication
     * @param bool $simplifyingParser
     * @param bool $debugMode
     */
    public function __construct(
        bool $allowImplicitMultiplication = true,
        bool $simplifyingParser = true,
        bool $debugMode = false
    ) {
        $this->lexer  = new StdMathLexer();
        $this->parser = new Parser($allowImplicitMultiplication, $simplifyingParser, $debugMode);
    }

    /**
     * @throws Exceptions\SyntaxErrorException
     * @throws Exceptions\ExponentialException
     * @throws Exceptions\DivisionByZeroException
     * @throws Exceptions\UnknownOperatorException
     * @throws Exceptions\UnknownTokenException
     * @throws Exceptions\NullOperandException
     * @throws Exceptions\DelimeterMismatchException
     * @throws Exceptions\UnexpectedOperatorException
     */
    public function evaluate(string $expression, array $variables = []): mixed
    {
        $abstractSyntaxTree = $this->parse($expression);

        $evaluator = new StdMathEvaluator($variables);

        return $abstractSyntaxTree->accept($evaluator);
    }

    /**
     * @throws Exceptions\SyntaxErrorException
     * @throws Exceptions\UnknownOperatorException
     * @throws Exceptions\ExponentialException
     * @throws Exceptions\DivisionByZeroException
     * @throws Exceptions\NullOperandException
     * @throws Exceptions\UnknownTokenException
     * @throws Exceptions\DelimeterMismatchException
     * @throws Exceptions\UnexpectedOperatorException
     */
    public function parse(string $expression): Node
    {
        $this->tokens = $this->lexer->tokenize($expression);
        $this->tree   = $this->parser->parse($this->tokens);

        return $this->tree;
    }
}

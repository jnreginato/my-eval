<?php

declare(strict_types=1);

namespace MyEval;

use MyEval\Lexing\StdMathLexer;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Parser;
use MyEval\Solving\RationalEvaluator;

/**
 * Convenience class for using the library.
 *
 * RationalMathEval is a wrapper for the StdMathLexer, Parser and RationalEvaluator class, and if you do not require any
 * tweaking, this is the most straightforward way to use the MyEval library.
 *
 * ## Example usage:
 *
 * ~~~{.php}
 * use MyEval\RationalMathEval;
 *
 * $evaluator = new RationalMathEval();
 * $value = $evaluator->evaluate('2*x + y', [ 'x' => '1/3', 'y' => -2 ]);
 *
 * however, if you require a more complex calc, use:
 *
 * ~~~{.php}
 * use MyEval\Solving\RationalEvaluator;
 * use MyEval\Lexing\StdMathLexer;
 * use MyEval\Parsing\Parser;
 *
 * // Tokenize
 * $lexer = new StdMathLexer();
 * $tokens = $lexer->tokenize('2*x + y');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Do whatever you want with the parsed expression, for example evaluate it.
 * $evaluator = new RationalEvaluator([ 'x' => '1/3', 'y' => -2 ]);
 * $value = $ast->accept($evaluator);
 * ~~~
 */
class RationalMathEval extends AbstractEvaluator
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

        $evaluator = new RationalEvaluator($variables);

        return $abstractSyntaxTree->accept($evaluator);
    }

    /**
     * @throws Exceptions\SyntaxErrorException
     * @throws Exceptions\DivisionByZeroException
     * @throws Exceptions\ExponentialException
     * @throws Exceptions\UnknownOperatorException
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

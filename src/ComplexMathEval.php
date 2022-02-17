<?php

declare(strict_types=1);

namespace MyEval;

use MyEval\Exceptions\DelimeterMismatchException;
use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Exceptions\UnknownTokenException;
use MyEval\Lexing\ComplexMathLexer;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Parser;
use MyEval\Solving\ComplexEvaluator;

/**
 * Convenience class for using the library.
 *
 * ComplexMathEval is a wrapper for the ComplexMathLexer, Parser and ComplexEvaluator classes, and if you do not require
 * any tweaking, this straightforward way to use the MyEval library.
 *
 * ## Example usage:
 *
 * ~~~{.php}
 * use MyEval\ComplexMathEval;
 *
 * $evaluator = new ComplexMathEval();
 * $value = $evaluator->evaluate('2x + 3i', [ 'x' => 3]);
 *
 * however, if you require a more complex calc, use:
 *
 * ~~~{.php}
 * use MyEval\Solving\ComplexEvaluator;
 * use MyEval\Lexing\ComplexMathLexer;
 * use MyEval\Parsing\Parser;
 *
 * // Tokenize
 * $lexer = new ComplexMathLexer();
 * $tokens = $lexer->tokenize('2x + 3i');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Do whatever you want with the parsed expression, for example evaluate it.
 * $evaluator = new ComplexEvaluator([ 'x' => '3' ]);
 * $value = $ast->accept($evaluator);
 * ~~~
 */
class ComplexMathEval extends AbstractEvaluator
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
        $this->lexer  = new ComplexMathLexer();
        $this->parser = new Parser($allowImplicitMultiplication, $simplifyingParser, $debugMode);
    }

    /**
     * @param string $expression
     * @param array  $variables
     *
     * @return mixed
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws UnknownOperatorException
     * @throws NullOperandException
     * @throws UnknownTokenException
     * @throws DelimeterMismatchException
     * @throws UnexpectedOperatorException
     */
    public function evaluate(string $expression, array $variables = []): mixed
    {
        $abstractSyntaxTree = $this->parse($expression);

        $evaluator = new ComplexEvaluator($variables);

        return $abstractSyntaxTree->accept($evaluator);
    }

    /**
     * @throws SyntaxErrorException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws UnknownOperatorException
     * @throws NullOperandException
     * @throws UnknownTokenException
     * @throws DelimeterMismatchException
     * @throws UnexpectedOperatorException
     */
    public function parse(string $expression): Node
    {
        $this->tokens = $this->lexer->tokenize($expression);
        $this->tree   = $this->parser->parse($this->tokens);

        return $this->tree;
    }
}

<?php

declare(strict_types=1);

namespace MyEval;

use MyEval\Lexing\LogicLexer;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Parser;
use MyEval\Solving\LogicEvaluator;

/**
 * Convenience class for using the library.
 *
 * LogicEval is a wrapper for the LogicLexer, Parser and LogicEvaluator classes, and if you do not require any tweaking,
 * this is the most straightforward way to use the MyEval library.
 *
 * ## Example usage:
 *
 * ~~~{.php}
 * use MyEval\LogicEval;
 *
 * $evaluator = new LogicEval();
 * $value = $evaluator->evaluate('IF(x > y; 9; 8)', [ 'x' => 5, 'y' => 4 ]);
 *
 * however, if you require a more complex calc, use:
 *
 * ~~~{.php}
 * use MyEval\Solving\LogicEvaluator;
 * use MyEval\Lexing\LogicLexer;
 * use MyEval\Parsing\Parser;
 *
 * // Tokenize
 * $lexer = new LogicLexer();
 * $tokens = $lexer->tokenize('IF(x > y; 9; 8)');
 *
 * // Parse
 * $parser = new Parser();
 * $ast = $parser->parse($tokens);
 *
 * // Do whatever you want with the parsed expression, for example evaluate it.
 * $evaluator = new LogicEvaluator([ 'x' => 5, 'y' => 4 ]);
 * $value = $ast->accept($evaluator);
 * ~~~
 */
class LogicEval extends AbstractEvaluator
{
    /**
     * @param bool $allowImplicitMultiplication
     * @param bool $simplifyingParser
     * @param bool $debugMode
     */
    public function __construct(
        bool $allowImplicitMultiplication = false,
        bool $simplifyingParser = true,
        bool $debugMode = false
    ) {
        $this->lexer  = new LogicLexer();
        $this->parser = new Parser($allowImplicitMultiplication, $simplifyingParser, $debugMode);
    }

    /**
     * @param string $expression
     * @param array  $variables
     *
     * @return mixed
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

        $evaluator = new LogicEvaluator($variables);

        return $abstractSyntaxTree->accept($evaluator);
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
    public function parse(string $expression): Node
    {
        $this->tokens = $this->lexer->tokenize($expression);
        $this->tree   = $this->parser->parse($this->tokens);

        return $this->tree;
    }
}

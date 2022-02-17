<?php

declare(strict_types=1);

namespace MyEval;

use MyEval\Lexing\Lexer;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Parser;

/**
 * Class AbstractEvaluator
 */
abstract class AbstractEvaluator
{
    protected Lexer $lexer;

    protected Parser $parser;

    protected array $tokens;

    protected Node $tree;

    /**
     * @return Lexer
     */
    public function getLexer(): Lexer
    {
        return $this->lexer;
    }

    /**
     * @return Parser
     */
    public function getParser(): Parser
    {
        return $this->parser;
    }

    /**
     * @return array
     */
    public function getTokenList(): array
    {
        return $this->tokens;
    }

    /**
     * @return Node
     */
    public function getTree(): Node
    {
        return $this->tree;
    }

    /**
     * @param Lexer $lexer
     *
     * @return void
     */
    public function replaceLexer(Lexer $lexer): void
    {
        $this->lexer = $lexer;
    }

    /**
     * @param Parser $parser
     *
     * @return void
     */
    public function replaceParser(Parser $parser): void
    {
        $this->parser = $parser;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function allowImplicitMultiplication(bool $flag): void
    {
        $this->parser->allowImplicitMultiplication($flag);
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setSimplifying(bool $flag): void
    {
        $this->parser->setSimplifying($flag);
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setDebugMode(bool $flag): void
    {
        $this->parser->setDebugMode($flag);
    }

    /**
     * @param string $expression
     * @param array  $variables
     *
     * @return mixed
     */
    abstract public function evaluate(string $expression, array $variables = []): mixed;

    /**
     * @param string $expression
     *
     * @return Node
     */
    abstract public function parse(string $expression): Node;
}

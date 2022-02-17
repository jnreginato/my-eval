<?php

declare(strict_types=1);

namespace MyEval\Parsing;

use LucidFrame\Console\ConsoleTable;
use MyEval\Exceptions\DelimeterMismatchException;
use MyEval\Exceptions\DivisionByZeroException;
use MyEval\Exceptions\ExponentialException;
use MyEval\Exceptions\NullOperandException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Exceptions\UnexpectedOperatorException;
use MyEval\Exceptions\UnknownOperatorException;
use MyEval\Lexing\Token;
use MyEval\Lexing\TokenType;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\RationalNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\AbstractExpressionNode;
use MyEval\Parsing\Nodes\Operator\AbstractOperatorNode;
use MyEval\Parsing\Nodes\Operator\CloseBraceNode;
use MyEval\Parsing\Nodes\Operator\CloseParenthesisNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\OpenBraceNode;
use MyEval\Parsing\Nodes\Operator\OpenParenthesisNode;
use MyEval\Parsing\Nodes\Operator\PostfixExpressionNode;
use MyEval\Parsing\Nodes\Operator\TerminatorNode;
use MyEval\Parsing\Nodes\Operator\TernaryExpressionNode;
use MyEval\Parsing\Nodes\Operator\UnmappedNode;
use MyEval\Parsing\Operations\OperationBuilder;
use MyEval\Parsing\Traits\Numeric;

use function get_class;

/**
 * Mathematical expression parser, based on the shunting yard algorithm.
 *
 * Parse a token string into an abstract syntax tree (AST).
 *
 * As the parser loops over the individual tokens, two stacks are kept up to date.
 * One stack ($operatorStack) consists of hitherto unhandled tokens corresponding to ''operators'' (unary and binary
 * operators, function applications and parenthesis) and a stack of parsed sub-expressions (the $operandStack).
 *
 * If the current token is a terminal token (number, variable or constant), a corresponding node is pushed onto the
 * operandStack. Otherwise, the precedence of the current token is compared to the top element(t) on the operatorStack,
 * and as long as the current token has lower precedence, we keep popping operators from the stack to construct more
 * complicated subexpressions together with the top items on the operandStack.
 *
 * Once the token list is empty, we pop the remaining operators as above, and if the formula was well-formed, the only
 * thing remaining on the operandStack is a completely parsed AST, which we return.
 */
class Parser
{
    use Numeric;

    /**
     * @var array $tokens Token[] list of tokens to process.
     */
    protected array $tokens;

    /**
     * @var Stack $operandStack Stack of operands waiting to process.
     */
    protected Stack $operandStack;

    /**
     * @var Stack $operatorStack Stack of operators waiting to process.
     */
    protected Stack $operatorStack;

    /**
     * @var Node|null
     */
    private static Node|null $lastNode = null;

    /**
     * @param bool $allowImplicitMultiplication Determine if the parser allows implicit multiplication.
     * @param bool $simplifyingParser           Determine if apply a simplification of the given expression.
     * @param bool $debugMode                   Determine if enable/disable debug mode.
     */
    public function __construct(
        private bool $allowImplicitMultiplication = true,
        private bool $simplifyingParser = true,
        private bool $debugMode = false
    ) {
    }

    /**
     * Parse list of tokens.
     *
     * @param Token[] $tokens Array of input tokens.
     *
     * @return Node AST representing the parsed expression.
     * @throws SyntaxErrorException
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws NullOperandException
     * @throws ExponentialException
     */
    public function parse(array $tokens): Node
    {
        // Filter away any whitespace.
        $tokens = $this->filterTokens($tokens);

        // Insert missing implicit multiplication tokens.
        if ($this->allowImplicitMultiplication) {
            $tokens = $this->parseImplicitMultiplication($tokens);
        }

        $this->tokens = $tokens;

        // Perform the actual parsing.
        return $this->shuntingYard($tokens);
    }

    /**
     * Remove Whitespace from the token list.
     *
     * @param array $tokens Input list of tokens.
     *
     * @return Token[]
     */
    protected function filterTokens(array $tokens): array
    {
        $filteredTokens = array_filter($tokens, static function (Token $token) {
            return $token->type !== TokenType::WHITESPACE;
        });

        // Return the array values only, because array_filter preserves the keys.
        return array_values($filteredTokens);
    }

    /**
     * Insert multiplication tokens where needed (taking care of implicit multiplication).
     *
     * @param array $tokens Input list of tokens (Token[]).
     *
     * @return Token[]
     */
    protected function parseImplicitMultiplication(array $tokens): array
    {
        $result    = [];
        $lastToken = null;
        foreach ($tokens as $token) {
            if (Token::canFactorsInImplicitMultiplication($lastToken, $token)) {
                $result[] = new Token('*', TokenType::MULTIPLICATION_OPERATOR);
            }
            $lastToken = $token;
            $result[]  = $token;
        }

        return $result;
    }

    /**
     * Implementation of the shunting yard parsing algorithm.
     *
     * @param array $tokens Token[] array of tokens to process.
     *
     * @return Node AST of the parsed expression.
     * @throws SyntaxErrorException
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws NullOperandException
     * @throws UnexpectedOperatorException
     * @throws ExponentialException
     */
    private function shuntingYard(array $tokens): Node
    {
        $this->operatorStack = new Stack();
        $this->operandStack  = new Stack();

        // Remember the last token handled, this is done to recognize unary operators.
        self::$lastNode = null;

        // Loop over the tokens.
        foreach ($tokens as $token) {
            $debug[] = [
                'token'          => (string)$token,
                'rpn_output'     => (string)$this->operandStack,
                'operator_stack' => (string)$this->operatorStack,
            ];
            $this->processToken($token);
        }

        $this->printTable($debug ?? []);

        // Pop remaining operators.
        while (!$this->operatorStack->isEmpty()) {
            /** @var InfixExpressionNode|TernaryExpressionNode $node */
            $node = $this->operatorStack->pop();
            $node = $this->populateNode($node);
            $this->operandStack->push($node);
        }

        // Stack should be empty here.
        if ($this->operandStack->count() > 1) {
            throw new SyntaxErrorException();
        }

        return $this->operandStack->pop();
    }

    /**
     * Process current token.
     *
     * @param Token $token
     *
     * @return void
     * @throws DivisionByZeroException
     * @throws DelimeterMismatchException
     * @throws SyntaxErrorException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws NullOperandException
     * @throws ExponentialException
     */
    private function processToken(Token $token): void
    {
        $node = Node::factory($token);

        switch (get_class($node)) {
            // Operands
            case IntegerNode::class:
            case RationalNode::class:
            case FloatNode::class:
            case BooleanNode::class:
            case VariableNode::class:
            case ConstantNode::class:
                $this->handleOperand($node);
                break;

            // Operators
            case InfixExpressionNode::class:
                $this->handleInfixOperator($node, $token, self::$lastNode);
                break;

            case PostfixExpressionNode::class:
                $this->handlePostfixOperator($node);
                break;

            case TernaryExpressionNode::class:
                $this->handleTernaryOperator($node);
                break;

            case FunctionNode::class:
                $this->handleFunctionOperator($node);
                break;

            case OpenParenthesisNode::class:
                $this->handleOpenParenthesis($node);
                break;

            case CloseParenthesisNode::class:
                $this->handleCloseParenthesis();
                break;

            case OpenBraceNode::class:
                $this->handleOpenBrace($node);
                break;

            case CloseBraceNode::class:
                $this->handleCloseBrace();
                break;

            case TerminatorNode::class:
            case UnmappedNode::class:
            default:
                break;
        }

        // Remember the current token (if it hasn't been nulled, for example being a unary +).
        if (
            !$node instanceof UnmappedNode &&
            !$node instanceof CloseParenthesisNode &&
            !(
                $node instanceof InfixExpressionNode &&
                $this->isUnary($node, self::$lastNode) &&
                $token->type === TokenType::ADDITION_OPERATOR
            )
        ) {
            self::$lastNode = $node;
        }
    }

    /**
     * Push terminal tokens on the operandStack.
     *
     * @param Node $node
     *
     * @return void
     */
    private function handleOperand(Node $node): void
    {
        $this->operandStack->push($node);
    }

    /**
     * @param InfixExpressionNode $node
     * @param Token               $token
     * @param Node|null           $lastNode
     *
     * @return void
     * @throws DivisionByZeroException
     * @throws DelimeterMismatchException
     * @throws SyntaxErrorException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws NullOperandException
     * @throws ExponentialException
     */
    private function handleInfixOperator(InfixExpressionNode $node, Token $token, ?Node $lastNode): void
    {
        // Check for unary minus and unary plus.
        if ($this->isUnary($node, $lastNode)) {
            // Unary +, just ignore it.
            if ($token->type === TokenType::ADDITION_OPERATOR) {
                return;
            }
            // Unary -, replace the token.
            if ($token->type === TokenType::SUBTRACTION_OPERATOR) {
                $node = new InfixExpressionNode('~', null, null);
            }
        }

        // Pop operators with higher priority.
        while ($node->lowerPrecedenceThan($this->operatorStack->peek())) {
            /** @var InfixExpressionNode|TernaryExpressionNode $popped */
            $popped = $this->operatorStack->pop();
            $popped = $this->populateNode($popped);
            $this->operandStack->push($popped);
        }

        $this->operatorStack->push($node);
    }

    /**
     * Handle postfix operators.
     *
     * @param PostfixExpressionNode $node
     *
     * @return void
     * @throws SyntaxErrorException
     */
    private function handlePostfixOperator(PostfixExpressionNode $node): void
    {
        $operand = $this->operandStack->pop();
        if ($operand === null) {
            throw new SyntaxErrorException();
        }
        $this->operandStack->push(new FunctionNode($node->operator, $operand));
    }

    /**
     * Push ternary tokens on the operandStack.
     *
     * @param TerminatorNode $node
     *
     * @return void
     */
    private function handleTernaryOperator(Node $node): void
    {
        $this->operatorStack->push($node);
    }

    /**
     * Push function applications onto the operatorStack.
     *
     * @param FunctionNode $node
     *
     * @return void
     */
    private function handleFunctionOperator(FunctionNode $node): void
    {
        $this->operatorStack->push($node);
    }

    /**
     * Push open parenthesis `(` onto the operatorStack.
     *
     * @param OpenParenthesisNode $node
     *
     * @return void
     */
    private function handleOpenParenthesis(OpenParenthesisNode $node): void
    {
        $this->operatorStack->push($node);
    }

    /**
     * Handle a closing parenthesis.
     *
     * Popping operators off the operator stack until we find a matching opening parenthesis.
     *
     * @throws DelimeterMismatchException
     * @throws SyntaxErrorException
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws NullOperandException
     * @throws ExponentialException
     */
    protected function handleCloseParenthesis(): void
    {
        // Flag, checking for mismatching parentheses.
        $clean = false;

        // Pop operators off the operatorStack until its empty, or we find an opening parenthesis, building
        // subexpressions on the operandStack as we go.
        /** @var InfixExpressionNode|TernaryExpressionNode $popped */
        while ($popped = $this->operatorStack->pop()) {
            // ok, we have our matching opening parenthesis
            if ($popped instanceof OpenParenthesisNode) {
                $clean = true;
                break;
            }

            $node = $this->populateNode($popped);
            $this->operandStack->push($node);
        }

        // Throw an error if the parenthesis couldn't be matched.
        if (!$clean) {
            throw new DelimeterMismatchException();
        }

        // Check to see if the parenthesis pair was in fact part of a function application.
        // If so, create the corresponding FunctionNode and push it onto the operandStack.
        $previous = $this->operatorStack->peek();
        if ($previous instanceof FunctionNode) {
            /** @var FunctionNode $node */
            $node    = $this->operatorStack->pop();
            $operand = $this->operandStack->pop();
            $node->setOperand($operand);
            $this->operandStack->push($node);
        }
    }

    /**
     * Push open brace `{` onto the operatorStack.
     *
     * @param OpenBraceNode $node
     *
     * @return void
     */
    private function handleOpenBrace(OpenBraceNode $node): void
    {
        $this->operatorStack->push($node);
    }

    /**
     * Handle a closing brace.
     *
     * Popping operators off the operator stack until we find a matching opening brace.
     *
     * @throws DelimeterMismatchException
     * @throws SyntaxErrorException
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws NullOperandException
     * @throws ExponentialException
     */
    protected function handleCloseBrace(): void
    {
        // Flag, checking for mismatching brace.
        $clean = false;

        // Pop operators off the operatorStack until its empty, or we find an opening brace.
        /** @var InfixExpressionNode|TernaryExpressionNode $popped */
        while ($popped = $this->operatorStack->pop()) {
            if ($popped instanceof OpenBraceNode) {
                $clean = true;
                break;
            }

            $node = $this->populateNode($popped);
            $this->operandStack->push($node);
        }

        // Throw an error if the brace couldn't be matched.
        if (!$clean) {
            throw new DelimeterMismatchException('{');
        }
    }

    /**
     * Populate node (ExpressionNode or IfNode) with operands.
     *
     * @param AbstractOperatorNode $node
     *
     * @return Node
     * @throws DelimeterMismatchException
     * @throws DivisionByZeroException
     * @throws ExponentialException
     * @throws NullOperandException
     * @throws SyntaxErrorException
     * @throws UnexpectedOperatorException
     * @throws UnknownOperatorException
     */
    protected function populateNode(AbstractOperatorNode $node): Node
    {
        if ($node instanceof FunctionNode) {
            throw new DelimeterMismatchException($node->operator);
        }

        if ($node instanceof OpenParenthesisNode) {
            throw new DelimeterMismatchException($node->operator);
        }

        if (!$node instanceof AbstractExpressionNode) {
            throw new SyntaxErrorException();
        }

        if ($node->operator === '~') {
            return $this->populateUnaryNode();
        }

        $right = $this->operandStack->pop();
        $left  = $this->operandStack->pop();

        if ($right === null || $left === null) {
            throw new SyntaxErrorException();
        }

        $node->setLeft($left);
        $node->setRight($right);

        if ($node instanceof TernaryExpressionNode) {
            $node->setCondition($this->operandStack->pop());
        }

        return $this->simplifyingParser
            ? (new OperationBuilder())->simplify($node)
            : $node;
    }

    /**
     * Populate unary node with operands.
     *
     * @return Node
     * @throws DivisionByZeroException
     * @throws SyntaxErrorException
     * @throws UnknownOperatorException
     * @throws UnexpectedOperatorException
     * @throws NullOperandException
     * @throws ExponentialException
     */
    private function populateUnaryNode(): Node
    {
        $left = $this->operandStack->pop();

        if ($left === null) {
            throw new SyntaxErrorException();
        }

        if ($this->simplifyingParser && $this->isNumeric($left)) {
            return $this->simplifyUnary($left);
        }

        $node = new InfixExpressionNode('-', $left, null);

        return $this->simplifyingParser
            ? (new OperationBuilder())->simplify($node)
            : $node;
    }

    /**
     * @throws DivisionByZeroException
     */
    private function simplifyUnary(Node $left): Node
    {
        return match (get_class($left)) {
            IntegerNode::class  => new IntegerNode(-$left->value),
            RationalNode::class => new RationalNode(-$left->getNumerator(), $left->getDenominator()),
            FloatNode::class    => new FloatNode(-$left->value),
            default             => $left
        };
    }

    /**
     * Determine if $node is in fact a unary operator.
     *
     * If $node can be a unary operator (i.e. is a '+' or '-' node), **and** this is the first node we parse or the
     * previous node was a SubExpressionNode, i.e. an opening parenthesis, or the previous node was already a unary
     * minus, this means that the current node is in fact a unary '+' or '-' and we return true, otherwise return false.
     *
     * @param Node      $node     Current node.
     * @param Node|null $lastNode Previous node handled by the Parser.
     *
     * @return bool
     */
    protected function isUnary(Node $node, ?Node $lastNode): bool
    {
        if (!$node instanceof InfixExpressionNode) {
            return false;
        }

        if (!($node->canBeUnary())) {
            return false;
        }

        // Unary if it is the first token.
        if ($this->operatorStack->isEmpty() && $this->operandStack->isEmpty()) {
            return true;
        }

        // Or if the previous token was '(' or '{'.
        if ($lastNode instanceof OpenParenthesisNode || $lastNode instanceof OpenBraceNode) {
            return true;
        }

        // Or if the previous token was ';'.
        if ($lastNode instanceof TerminatorNode) {
            return true;
        }

        // Or last node was already a unary minus.
        if ($lastNode instanceof InfixExpressionNode && $lastNode->operator === '~') {
            return true;
        }

        return false;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function allowImplicitMultiplication(bool $flag): void
    {
        $this->allowImplicitMultiplication = $flag;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setSimplifying(bool $flag): void
    {
        $this->simplifyingParser = $flag;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setDebugMode(bool $flag): void
    {
        $this->debugMode = $flag;
    }

    /**
     * Print a table showing the result of Reverse Polish Notation by extending the shunting yard algorithm.
     * Just for debug.
     *
     * @param array $debug
     *
     * @return void
     */
    private function printTable(array $debug): void
    {
        $debug[] = [
            'token'          => '',
            'rpn_output'     => (string)$this->operandStack,
            'operator_stack' => (string)$this->operatorStack,
        ];

        if ($this->debugMode) {
            echo "\n";
            $tbl = new ConsoleTable();
            $tbl->setHeaders(['Token', 'RPN Output', 'Operator stack']);
            foreach ($debug ?? [] as $item) {
                $tbl->addRow([$item['token'], $item['rpn_output'], $item['operator_stack']]);
            }
            $tbl->display();
            echo "\n";
        }
    }
}

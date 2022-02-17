<?php

declare(strict_types=1);

namespace MyEval;

use MyEval\Exceptions\DelimeterMismatchException;
use MyEval\Exceptions\SyntaxErrorException;
use MyEval\Lexing\Token;
use MyEval\Lexing\TokenType;
use MyEval\Parsing\Nodes\Operand\ConstantNode;
use MyEval\Parsing\Nodes\Operand\FloatNode;
use MyEval\Parsing\Nodes\Operand\IntegerNode;
use MyEval\Parsing\Nodes\Operand\VariableNode;
use MyEval\Parsing\Nodes\Operator\FunctionNode;
use MyEval\Parsing\Nodes\Operator\InfixExpressionNode;
use MyEval\Solving\TreePrinter;
use PHPUnit\Framework\TestCase;

class StdMathEvalTest extends TestCase
{
    private StdMathEval $eval;

    public function setUp(): void
    {
        $this->eval = new StdMathEval();
    }

    private function assertNodesEqual($node1, $node2): void
    {
        $printer = new TreePrinter();
        $message = 'Node1: ' . $node1->accept($printer) . "\nNode 2: " . $node2->accept($printer) . "\n";

        static::assertTrue($node1->compareTo($node2), $message);
    }

    private function assertCompareNodes($text): void
    {
        $node1 = $this->eval->parse($text);
        $node2 = $this->eval->parse($text);

        $this->assertNodesEqual($node1, $node2);
    }

    /**
     * @return void
     */
    public function testCanCompareNodes(): void
    {
        $this->assertCompareNodes('3');
        $this->assertCompareNodes('x');
        $this->assertCompareNodes('x+y');
        $this->assertCompareNodes('sin(x)');
        $this->assertCompareNodes('(x)');
        $this->assertCompareNodes('1+x+y');
    }

    private function assertTokenEquals($value, $type, Token $token): void
    {
        static::assertEquals($value, $token->value);
        static::assertEquals($type, $token->type);
    }

    public function testCanGetTokenList(): void
    {
        $this->eval->parse('x+y');
        $tokens = $this->eval->getTokenList();

        $this->assertTokenEquals('x', TokenType::VARIABLE, $tokens[0]);
        $this->assertTokenEquals('+', TokenType::ADDITION_OPERATOR, $tokens[1]);
        $this->assertTokenEquals('y', TokenType::VARIABLE, $tokens[2]);
    }

    public function testCanGetTree(): void
    {
        $node = $this->eval->parse('1+x');
        $tree = $this->eval->getTree();
        $this->assertNodesEqual($node, $tree);
    }

    public function testCanParseSingleNumberExpression(): void
    {
        $node     = $this->eval->parse('3');
        $shouldBe = new IntegerNode(3);
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('3.5');
        $shouldBe = new FloatNode(3.5);
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testCanParseSingleVariable(): void
    {
        $node     = $this->eval->parse('x');
        $shouldBe = new VariableNode('x');

        $this->assertNodesEqual($node, $shouldBe);

        $node = $this->eval->parse('(x)');
        $this->assertNodesEqual($node, $shouldBe);

        $node = $this->eval->parse('((x))');
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testCanParseSingleConstant(): void
    {
        $node     = $this->eval->parse('pi');
        $shouldBe = new ConstantNode('pi');

        $this->assertNodesEqual($node, $shouldBe);

        $node = $this->eval->parse('(pi)');
        $this->assertNodesEqual($node, $shouldBe);

        $node = $this->eval->parse('((pi))');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('e');
        $shouldBe = new ConstantNode('e');

        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testCanParseBinaryExpression(): void
    {
        $node     = $this->eval->parse('x+y');
        $shouldBe = new InfixExpressionNode('+', new VariableNode('x'), new VariableNode('y'));

        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x-y');
        $shouldBe = new InfixExpressionNode('-', new VariableNode('x'), new VariableNode('y'));

        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x*y');
        $shouldBe = new InfixExpressionNode('*', new VariableNode('x'), new VariableNode('y'));

        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x/y');
        $shouldBe = new InfixExpressionNode('/', new VariableNode('x'), new VariableNode('y'));

        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x^y');
        $shouldBe = new InfixExpressionNode('^', new VariableNode('x'), new VariableNode('y'));

        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testCanParseWithCorrectAssociativity(): void
    {
        $node     = $this->eval->parse('x+y+z');
        $shouldBe = new InfixExpressionNode(
            '+',
            new InfixExpressionNode('+', new VariableNode('x'), new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x-y-z');
        $shouldBe = new InfixExpressionNode(
            '-',
            new InfixExpressionNode('-', new VariableNode('x'), new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x*y*z');
        $shouldBe = new InfixExpressionNode(
            '*',
            new InfixExpressionNode('*', new VariableNode('x'), new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x/y/z');
        $shouldBe = new InfixExpressionNode(
            '/',
            new InfixExpressionNode('/', new VariableNode('x'), new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x^y^z');
        $shouldBe = new InfixExpressionNode(
            '^',
            new VariableNode('x'),
            new InfixExpressionNode('^', new VariableNode('y'), new VariableNode('z'))
        );
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testCanParseThreeTerms(): void
    {
        $x  = new VariableNode('x');
        $mx = new InfixExpressionNode('-', $x, null);

        $node     = $this->eval->parse('x+y+z');
        $shouldBe = new InfixExpressionNode(
            '+',
            new InfixExpressionNode('+', new VariableNode('x'), new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x+y-z');
        $shouldBe = new InfixExpressionNode(
            '-',
            new InfixExpressionNode('+', new VariableNode('x'), new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x-y+z');
        $shouldBe = new InfixExpressionNode(
            '+',
            new InfixExpressionNode('-', new VariableNode('x'), new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x-y-z');
        $shouldBe = new InfixExpressionNode(
            '-',
            new InfixExpressionNode('-', new VariableNode('x'), new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        // First term with unary minus

        $node     = $this->eval->parse('-x+y+z');
        $shouldBe = new InfixExpressionNode(
            '+',
            new InfixExpressionNode('+', $mx, new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('-x+y-z');
        $shouldBe = new InfixExpressionNode(
            '-',
            new InfixExpressionNode('+', $mx, new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('-x-y+z');
        $shouldBe = new InfixExpressionNode(
            '+',
            new InfixExpressionNode('-', $mx, new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('-x-y-z');
        $shouldBe = new InfixExpressionNode(
            '-',
            new InfixExpressionNode('-', $mx, new VariableNode('y')),
            new VariableNode('z')
        );
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testCanParseWithCorrectPrecedence(): void
    {
        $x = new VariableNode('x');
        $y = new VariableNode('y');
        $z = new VariableNode('z');

        $node = $this->eval->parse('x+y*z');

        $factors  = new InfixExpressionNode('*', $y, $z);
        $shouldBe = new InfixExpressionNode('+', $x, $factors);

        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x*y+z');
        $factors  = new InfixExpressionNode('*', $x, $y);
        $shouldBe = new InfixExpressionNode('+', $factors, $z);

        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testCanParseParentheses(): void
    {
        $node     = $this->eval->parse('(x)');
        $shouldBe = new VariableNode('x');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('((x))');
        $shouldBe = new VariableNode('x');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('(x+1)');
        $shouldBe = $this->eval->parse('x+1');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('(x*y)');
        $shouldBe = $this->eval->parse('x*y');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('(x^y)');
        $shouldBe = $this->eval->parse('x^y');
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testImplicitMultiplication(): void
    {
        $node     = $this->eval->parse('2x');
        $shouldBe = $this->eval->parse('2*x');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('2xy');
        $shouldBe = $this->eval->parse('2*x*y');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('2x^2');
        $shouldBe = $this->eval->parse('2*x^2');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('2x^2y');
        $shouldBe = $this->eval->parse('2*x^2*y');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('(-x)2');
        $shouldBe = $this->eval->parse('(-x)*2');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('x^2y^2');
        $shouldBe = $this->eval->parse('x^2*y^2');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('(x+1)(x-1)');
        $shouldBe = $this->eval->parse('(x+1)*(x-1)');
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testCanParseUnaryOperators(): void
    {
        $node     = $this->eval->parse('-x');
        $shouldBe = new InfixExpressionNode('-', new VariableNode('x'), null);
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('+x');
        $shouldBe = new VariableNode('x');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('-x+y');
        $shouldBe = new InfixExpressionNode(
            '+',
            new InfixExpressionNode('-', new VariableNode('x'), null),
            new VariableNode('y')
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('-x*y');
        $shouldBe = $this->eval->parse('-(x*y)');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('-x^y');
        $shouldBe = $this->eval->parse('-(x^y)');
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('(-x)^y');
        $shouldBe = new InfixExpressionNode(
            '^',
            new InfixExpressionNode('-', new VariableNode('x'), null),
            new VariableNode('y')
        );
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function testSyntaxErrorException(): void
    {
        $this->expectException(SyntaxErrorException::class);
        $this->eval->parse('1+');
    }

    public function testSyntaxErrorException2(): void
    {
        $this->expectException(SyntaxErrorException::class);
        $this->eval->parse('**3');
    }

    public function testSyntaxErrorException3(): void
    {
        $this->expectException(SyntaxErrorException::class);
        $this->eval->parse('-');
    }

    public function testSyntaxErrorException4(): void
    {
        $this->expectException(SyntaxErrorException::class);
        $this->eval->parse('e^');
    }

    public function testParenthesisMismatchException(): void
    {
        $this->expectException(DelimeterMismatchException::class);
        $this->eval->parse('1+1)');

        $this->expectException(DelimeterMismatchException::class);
        $this->eval->parse('(1+1');
    }

    public function testCanParseUnbalancedParentheses(): void
    {
        $this->expectException(DelimeterMismatchException::class);
        $this->eval->parse('1(2');
    }

    public function testCanParseUnbalancedParentheses2(): void
    {
        $this->expectException(DelimeterMismatchException::class);
        $this->eval->parse('1)2');
    }

    public function testCanEvaluateNode(): void
    {
        $f = $this->eval->parse('x+y');
        $this->assertEquals(3, $f->evaluate(['x' => 1, 'y' => 2]));
    }

    public function testParserWithoutImplicitMultiplication(): void
    {
        $this->eval->allowImplicitMultiplication(false);
        $this->expectException(SyntaxErrorException::class);
        $this->eval->parse('2x');
    }

    public function testNonSimplifyingParser(): void
    {
        $this->eval->setSimplifying(false);

        $node     = $this->eval->parse('3+5');
        $shouldBe = new InfixExpressionNode('+', new IntegerNode(3), new IntegerNode(5));
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('3-5');
        $shouldBe = new InfixExpressionNode('-', new IntegerNode(3), new IntegerNode(5));
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('-3');
        $shouldBe = new InfixExpressionNode('-', new IntegerNode(3), null);
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function canParseFactorial(): void
    {
        $node     = $this->eval->parse('3!4!');
        $shouldBe = new InfixExpressionNode(
            '*',
            new FunctionNode('!', new FloatNode(3)),
            new FunctionNode('!', new FloatNode(4))
        );
        $this->assertNodesEqual($node, $shouldBe);

        $node     = $this->eval->parse('-3!');
        $shouldBe = new InfixExpressionNode(
            '-',
            new FunctionNode('!', new FloatNode(3)),
            null
        );
        $this->assertNodesEqual($node, $shouldBe);
    }

    public function canParseInvalidFactorial(): void
    {
        $this->expectException(SyntaxErrorException::class);
        $this->eval->parse('!1');
    }

    public function canParseInvalidFactorial2(): void
    {
        $this->expectException(SyntaxErrorException::class);
        $this->eval->parse('1+!1');
    }
}

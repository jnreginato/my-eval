<?php

declare(strict_types=1);

namespace MyEval\Solving;

use MyEval\LogicEval;
use MyEval\Parsing\Nodes\Node;
use MyEval\Parsing\Nodes\Operand\BooleanNode;
use PHPUnit\Framework\TestCase;

/**
 * Class LogicEvaluatorTest
 */
class LogicEvaluatorTest extends TestCase
{
    private LogicEval $parser;

    private LogicEvaluator $evaluator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->parser = new LogicEval();

        $variables       = [];
        $this->evaluator = new LogicEvaluator($variables);

        parent::setUp();
    }

    /**
     * @param Node $ast
     *
     * @return mixed
     */
    private function evaluate(Node $ast): mixed
    {
        return $ast->accept($this->evaluator);
    }

    /**
     * @param string $data
     * @param        $expected
     *
     * @return void
     */
    private function assertResult(string $data, $expected): void
    {
        $result = $this->evaluate($this->parser->parse($data));

        static::assertSame($result, $expected);
    }

    /**
     * @return void
     */
    public function testCanEvaluateBoolean(): void
    {
        static::assertTrue((new BooleanNode('true'))->accept($this->evaluator));
        static::assertFalse((new BooleanNode('false'))->accept($this->evaluator));
    }

    /**
     * @return void
     */
    public function testCanEvaluateEqual(): void
    {
        $this->assertResult('IF (5>4) {0;} else {1;}', 0);
        $this->assertResult('IF (5<4) {0;} else {1;}', 1);
    }
}

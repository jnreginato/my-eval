<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use MyEval\Solving\LogicEvaluator;
use MyEval\Lexing\LogicLexer;
use MyEval\Parsing\Parser;

// $equation = 'IF (2<1) THEN 1 ELSE 0';
$equation = 'if ((3 < 2)) { 1+1; } else { 2^3; }';

// Tokenize
$lexer  = new LogicLexer();
$tokens = $lexer->tokenize($equation);

// Parse
$parser = new Parser(
    allowImplicitMultiplication: true,
    simplifyingParser: true,
    debugMode: true
);
$ast    = $parser->parse($tokens);

// Evaluate
$evaluator = new LogicEvaluator(['aplicaTransferencia' => true]);
$result    = $ast->accept($evaluator);

echo $result;

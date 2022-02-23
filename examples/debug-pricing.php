<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use MyEval\Solving\PricingEvaluator;
use MyEval\Lexing\PricingLexer;
use MyEval\Parsing\Parser;

// $equation = 'IF (2<1) THEN 1 ELSE 0';
// $equation = 'if (3 < 2) { return 1+1; } else { return 2^3; }';
// $equation = 'sqrt(4)';
// $equation = 'ending(100.01, .99)';
// $equation = "if (transfFrete <> 0.0 || nineEnding <> true) { preco } else { if(preco < 20) { preco } else { if(preco < 200) { ending(preco, .90) } else { if (preco < 2000) { ending(preco, 9.90) } else { ending(preco, 99.90) }}}}";
$equation = 'if ($transfFrete <> 0.0 || $nineEnding <> true) { return $preco; } else { return ending($preco, .90) }';

// Tokenize
$lexer  = new PricingLexer();
$tokens = $lexer->tokenize($equation);

// Parse
$parser = new Parser(
    allowImplicitMultiplication: true,
    simplifyingParser: true,
    debugMode: true
);
$ast    = $parser->parse($tokens);

// Evaluate
$evaluator = new PricingEvaluator(['$transfFrete' => 0.0, '$nineEnding' => true, '$preco' => 500.0]);
$result    = $ast->accept($evaluator);

echo $result;

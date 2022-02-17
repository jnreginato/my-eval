<?php

declare(strict_types=1);

use MyEval\Lexing\LogicLexer;
use MyEval\Parsing\Parser;
use MyEval\Solving\LogicEvaluator;

include __DIR__ . '/../vendor/autoload.php';

$calculate = [
    // Natural number
    '1',
    '4',

    // Integer
    '0',
    '-2',

    // Real number
    '3.0',
    '1.5',

    // Boolean
    'true',
    'TRUE',
    'false',
    'FALSE',

    // Variable
    'x',
    'VARIABLE',
    'TEST',
    'test',

    // Constant
    'pi',
    'e',
    'NAN',
    'INF',

    // Expression
    '2+1',
    '1+(-2)',
    '-x',
    'x+1',
    '3+x',
    '3-x',
    '3+x+1',
    '3-x-1',
    '1+(-x)',
    '-(x-1)',
    'x+y',
    'x+y+z',
    'x+y-z',
    'x-y-z',
    'x-y+z',
    '-x-y-z',
    'x+(-y)',
    '1-(-1)*x',
    'x*(-1)+(-2)*(-x)',

    '2(x+4)',
    '2/3',
    '4/6',
    '-1/2',
    '4/2',
    '1/2+1/2',
    '1/(-2)+1/2',
    '(x+1)(x+2)',
    'x/y',
    '20/x/5',
    'x/(y+z)',
    '(x+y)/(y+z)',
    '(x+y)/(z-w)',
    'x*y/z',
    'x/y*z',
    'x*y/(z*w)',
    'x*y/(z-w)',
    '3*x',
    '3*x*2',
    '3/x',

    'x^2',
    'x*y/(z^w)',
    '1+2x+3x^2',
    '(-1)^k',
    '(1/2)^k',
    '(-1/2)^k',
    'x^(2/3)',
    '(-1)^(-1)',
    'x^(y+z)',
    'x^y^z',
    'x^x^x',
    '(x^y)^z',
    'x^3',
    '0^(-1)',

    '2=2',
    '2=1',
    '-2=(-2)',
    '1=(-2)',
    'x=1',
    '3=x',
    '2>2',
    '2>1',
    '-2>(-2)',
    '1>(-2)',
    'x>1',
    '3>x',
    '2<2',
    '2<1',
    '-2<(-2)',
    '1<(-2)',
    'x<1',
    '3<x',
    '2<>2',
    '2<>1',
    '-2<>(-2)',
    '1<>(-2)',
    'x<>1',
    '3<>x',
    '2>=2',
    '2>=1',
    '-2>=(-2)',
    '1>=(-2)',
    'x>=1',
    '3>=x',
    '2<=2',
    '2<=1',
    '-2<=(-2)',
    '1<=(-2)',
    'x<=1',
    '3<=x',
    '2 AND 1',
    'true AND 1',
    'TRUE AND 1',
    '1 OR 2',
    '0 OR false',
    '0 OR FALSE',

    // Function
    'sin(x)*x',
    '(x+sin(x))/2',
    'sin(x)',
    'sin(pi)',
    'sin(pi/2)',
    'sin(pi/6)',
    '(2+sin(x))/(1-1/2)',
    'cos(x)',
    'tan(x)',
    'exp(x)',
    'log(x)',
    'log(2+x)',
    'ln(x)',
    'ln(2+x)',
    'sqrt(x)',
    'sqrt(x^2)',
    'asin(x)',
    'cos(pi)',
    'cos(pi/2)',
    'cos(pi/3)',
    'tan(pi)',
    'tan(pi/4)',
    'cot(pi/2)',
    'cot(pi/4)',
    'cot(x)',
    'arcsin(1)',
    'arcsin(1/2)',
    'arcsin(x)',
    'arcsin(2)',
    'arccos(0)',
    'arccos(1/2)',
    'arccos(x)',
    'arccos(2)',
    'arctan(1)',
    'arctan(x)',
    'arccot(1)',
    'arccot(x)',
    'log(-1)',
    'ln(-1)',
    'log10(x)',
    'sqrt(-2)',
    'sinh(0)',
    'sinh(x)',
    'cosh(0)',
    'cosh(x)',
    'tanh(0)',
    'tanh(x)',
    'coth(x)',
    'arsinh(0)',
    'arsinh(x)',
    'arcosh(1)',
    'arcosh(3)',
    'artanh(0)',
    'artanh(x)',
    'arcoth(3)',
    '0*log(0)',
    'exp(1)',
    'exp(2)',
    'exp(-1)',
    'exp(8)',
    'exp(22)',
    'ceil(1+2.3)',
    'floor(2*2.3)',
    'ceil(2*2.3)',
    'round(2*2.3)',

    // Postfix
    '3!',
    'x!',
    'e!',
    '(x+y)!',
    '(x+2)!',
    'sin(x)!',
    '(3!)!',
    '3!!',
    'x!!',
    '0!',
    '5!/(2!3!)',
    '5!!',
    '4.12124!',

    // Conditional
    'if (aplicaTransferencia = true) { 1+1; } else { 2^3; }',
    'IF (x<>1) THEN 1 ELSE 0',
    'if (2>1) { 11 } else { 0 }',
    'if (1 <> 1) {
        1;
    } else {
        0;
    }',
];
foreach ($calculate as $key => $calc) {
    // Tokenize
    $lexer  = new LogicLexer();
    $tokens = $lexer->tokenize($calc);

    // Parse
    $parser = new Parser(
        allowImplicitMultiplication: true,
        simplifyingParser: true,
        debugMode: true
    );
    $ast    = $parser->parse($tokens);

    // Evaluate
    $evaluator = new LogicEvaluator([
        'x'                   => '2.0',
        'y'                   => '2.1',
        'z'                   => '2',
        'w'                   => '3',
        'k'                   => '6',
        'i'                   => '5',
        'VARIABLE'            => 1,
        'TEST'                => 1,
        'test'                => 0,
        'aplicaTransferencia' => true,
        'precoAtual'          => 100,
    ]);
    $result    = $ast->accept($evaluator);

    echo $calc . ': ' . $result . "\n";
}

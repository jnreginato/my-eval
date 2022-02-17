# My-eval

[![Latest Stable Version](https://poser.pugx.org/j84reginato/my-eval/v/stable)](https://packagist.org/packages/j84reginato/my-eval) [![Total Downloads](https://poser.pugx.org/j84reginato/my-eval/downloads)](https://packagist.org/packages/j84reginato/my-eval)  [![License](https://poser.pugx.org/j84reginato/my-eval/license)](https://packagist.org/packages/j84reginato/my-eval)
[![Code Climate](https://codeclimate.com/github/j84reginato/my-eval/badges/gpa.svg)](https://codeclimate.com/github/j84reginato/my-eval)

## DESCRIPTION

PHP parser and evaluator library for mathematical and logical expressions.

Intended use: safe and reasonably efficient evaluation of user submitted formulas and/or logical expressions. The
library supports basic arithmetic and elementary functions, as well as variables and extra functions, ternary (
if/then/else) expressions and conditional, logical (conjunction and disjunction) and relational operations.

The lexer and parser produces an abstract syntax tree (AST) that can be traversed using a tree interpreter. The
math-parser library ships with three interpreters:

* an evaluator computing the value of the given expression.
* a differentiator transforming the AST into a (somewhat) simplied AST representing the derivative of the supplied
  expression.
* a rudimentary LaTeX output generator, useful for pretty printing expressions using MathJax

## EXAMPLES

It is possible to fine-tune the lexer and parser, but the library ships with a StdMathParser class, capable of
tokenizing and parsing standard mathematical expressions, including arithmetical operations as well as elementary
functions.

~~~{.php}
Use MyEval\Lexing\StdMathLexer;
use MyEval\Parsing\Parser;
use MyEval\Solving\StdMathEvaluator;

// Tokenize
$lexer = new StdMathLexer();
$tokens = $lexer->tokenize('1+2');

// Parse
// Generate an abstract syntax tree
$parser = new Parser();
$ast = $parser->parse($tokens);

// Do something with the AST, e.g. evaluate the expression:
$evaluator = new StdMathEvaluator();
$value = $ast->accept($evaluator);
echo $value;
~~~

More interesting example, containing variables:

~~~{.php}
Use MyEval\Lexing\StdMathLexer;
use MyEval\Parsing\Parser;
use MyEval\Solving\StdMathEvaluator;

// Tokenize
$lexer = new StdMathLexer();
$tokens = $lexer->tokenize('x+sqrt(y)');

// Parse
// Generate an abstract syntax tree
$parser = new Parser();
$ast = $parser->parse($tokens);

// Evaluate
$evaluator = new StdMathEvaluator([ 'x' => 2, 'y' => 3 ]);
$value = $ast->accept($evaluator);
~~~

We can do other things with the AST. The library ships with a differentiator, computing the (symbolic) derivative with
respect to a given variable.

~~~{.php}
use MyEval\Lexing\StdMathLexer;
use MyEval\Parsing\Parser;
use MyEval\Solving\Differentiator;
use MyEval\Solving\StdMathEvaluator;

// Tokenize
$lexer = new StdMathLexer();
$tokens = $lexer->tokenize('exp(2*x)-x*y');

// Parse
// Generate an abstract syntax tree
$parser = new Parser();
$ast = $parser->parse($tokens);

// Differentiate
$differentiator = new Differentiator('x');
$derivative = $ast->accept($differentiator);
$df = $derivative->accept($differentiator);

// Evaluate
// $df now contains the AST of '2*exp(x)-y' and can be evaluated further
$evaluator = new StdMathEvaluator([ 'x' => 1, 'y' => 2 ]);
$value = $df->accept($evaluator);
~~~

### Implicit multiplication

Another helpful feature is that the parser understands implicit multiplication. An expression as `2x` is parsed the same
as `2*x` and `xsin(x)cos(x)^2` is parsed as `x*sin(x)*cos(x)^2`.

Note that implicit multiplication has the same precedence as explicit multiplication. In particular, `xy^2z` is parsed
as `x*y^2*z` and **not** as `x*y^(2*z)`.

To make full use of implicit multiplication, the standard lexer only allows one-letter variables. (Otherwise, we
wouldn't know if `xy` should be parsed as `x*y` or as the single variable `xy`).

## DOCUMENTATION

For complete documentation, see the [TODO]()

## THANKS

The Lexer is based on the lexer described by Marc-Oliver Fiset in
his [blog](http://marcofiset.com/programming-language-implementation-part-1-lexer/).

The parser is a version of the "Shunting yard" algorithm, described for example
by [Theodore Norvell](http://www.engr.mun.ca/~theo/Misc/exp_parsing.htm#shunting_yard).

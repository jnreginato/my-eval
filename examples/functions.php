<?php

declare(strict_types=1);

use MyEval\ComplexMathEval;
use MyEval\Solving\ASCIIPrinter;
use MyEval\Solving\Differentiator;
use MyEval\Solving\LaTeXPrinter;
use MyEval\Solving\TreePrinter;
use MyEval\LogicEval;
use MyEval\RationalMathEval;
use MyEval\StdMathEval;

include __DIR__ . '/../vendor/autoload.php';

if (!function_exists('std_math_eval')) {
    function std_math_eval(string $expression, array $variables = [])
    {
        try {
            return (new StdMathEval())->evaluate($expression, $variables);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('logic_eval')) {
    function logic_eval(string $expression, array $variables = [])
    {
        try {
            return (new LogicEval())->evaluate($expression, $variables);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('rational_eval')) {
    function rational_eval(string $expression, array $variables = [])
    {
        try {
            return (new RationalMathEval())->evaluate($expression, $variables);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('complex_eval')) {
    function complex_eval(string $expression, array $variables = [])
    {
        try {
            return (new ComplexMathEval())->evaluate($expression, $variables);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('token_list')) {
    function token_list(string $expression)
    {
        try {
            $eval = new StdMathEval();
            $eval->parse($expression);
            $tokens = json_encode($eval->getTokenList(), JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        return $tokens;
    }
}

if (!function_exists('tree_printer')) {
    function tree_printer(string $expression)
    {
        $eval        = new StdMathEval();
        $treePrinter = new TreePrinter();

        try {
            $eval->parse($expression);
            $tree   = $eval->getTree();
            $result = 'TreePrinter: ' . $tree->accept($treePrinter) . "\n";
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        return $result;
    }
}

if (!function_exists('latex_printer')) {
    function latex_printer(string $expression)
    {
        $eval         = new StdMathEval();
        $laTeXPrinter = new LaTeXPrinter();

        try {
            $eval->parse($expression);
            $tree   = $eval->getTree();
            $result = 'LaTeXPrinter: ' . $tree->accept($laTeXPrinter) . "\n";
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        return $result;
    }
}

if (!function_exists('ascii_printer')) {
    function ascii_printer(string $expression)
    {
        $eval        = new StdMathEval();
        $treePrinter = new ASCIIPrinter();

        try {
            $eval->parse($expression);
            $tree   = $eval->getTree();
            $result = 'String conversion: ' . $tree->accept($treePrinter) . "\n";
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        return $result;
    }
}

if (!function_exists('derivative')) {
    function derivative(string $expression)
    {
        $eval             = new StdMathEval();
        $latexTreePrinter = new LaTeXPrinter();
        $differentiator   = new Differentiator('x');

        try {
            $eval->parse($expression);
            $tree   = $eval->getTree();
            $result = $tree
                ->accept($differentiator)
                ->accept($latexTreePrinter);
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        return $result;
    }
}

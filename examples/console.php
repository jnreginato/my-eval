<?php

declare(strict_types=1);

require __DIR__. '/functions.php';

if (empty($argv[1])) {
    echo 'Equação não informada.';
    exit(1);
}
define('EXPRESSION', $argv[1]);

try {
    define('VARIABLES', (json_decode($argv[2] ?? '[]', true, 512, JSON_THROW_ON_ERROR)) ?? []);
} catch (Throwable) {
    echo 'Erro ao processar variáveis.';
    exit(1);
}

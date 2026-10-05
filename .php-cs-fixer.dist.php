<?php

$paths = [
    __DIR__ . '/srv',
    __DIR__ . '/lib',
    __DIR__ . '/scg',
    __DIR__ . '/scp',
    __DIR__ . '/tes',
    __DIR__ . '/ent',
    __DIR__ . '/poa',
    __DIR__ . '/rep',
];

$finder = PhpCsFixer\Finder::create()
    ->in($paths)
    ->name('*.php')
    ->notPath('vendor')
    ->notPath('PhpSpreadsheet-1.29.12/vendor')
    ->notPath('lib/PHPExcel')
    ->notPath('rep/lib/PhpSpreadsheet-1.29.12')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setUsingCache(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => ['=>' => 'align_single_space_minimal'],
        ],
        'blank_line_after_opening_tag' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => false,
        'single_quote' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
    ])
    ->setFinder($finder);

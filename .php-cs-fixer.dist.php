<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        // Most existing files end without a trailing newline: not enforced, so the pre-commit hook does not
        // rewrite them all.
        'single_blank_line_at_eof' => false,
    ])
    ->setFinder($finder);

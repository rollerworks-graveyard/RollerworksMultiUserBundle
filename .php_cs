<?php

use Symfony\CS\FixerInterface;
use Symfony\CS\Tokens;

$finder = Symfony\CS\Finder\DefaultFinder::create();
/** @var \Symfony\Component\Finder\Finder $finder */
$finder
    ->exclude('docs')
    ->exclude('bin')
    ->notName('*.php.twig')

    ->files()->filter(function (\SplFileInfo $file) {
        $path = str_replace('\\', '/', $file->getPath()).'/';

        if (true === strpos($path, '/Resources/')) {
            return false;
        }

        return true;
    })
    ->files()->filter(function (\SplFileInfo $file) {
        if (preg_match('{Kernel}i', str_replace('\\', '/', $file->getPath()))) {
           return false;
        }

        return true;
    })
;

if (file_exists('local.php_cs')) {
    require 'local.php_cs';
}

$fixers = array(
    'encoding',
    'short_tag',
    'braces',
    'elseif',
    'eof_ending',
    'function_declaration',
    'indentation',
    'line_after_namespace',
    'linefeed',
    'lowercase_constants',
    'lowercase_keywords',
    'multiple_use',
    'php_closing_tag',
    'trailing_spaces',
    'concat_without_spaces',
    'extra_empty_lines',
    'include',
    'multiline_array_trailing_comma',
    'new_with_braces',
    'object_operator',
    'operators_spaces',
    'phpdoc_params',
    'return',
    'single_array_no_trailing_comma',
    'spaces_cast',
    'standardize_not_equal',
    'ternary_spaces',
    'unused_use',
    'whitespacy_lines',
    'ordered_use',
    //'short_array_syntax',
);

return Symfony\CS\Config\Config::create()
    ->fixers($fixers)
    ->finder($finder)
;

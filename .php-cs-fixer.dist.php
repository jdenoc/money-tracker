<?php

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->in(__DIR__)
    ->notPath('_ide_helper.php')    // excludes this file from analysis
    ->exclude('bootstrap/cache')
    ->exclude('database/snapshots')
    ->exclude('docs')
    ->exclude('public')
    ->exclude('node_modules')
    ->exclude('resources/css')
    ->exclude('resources/js')
    ->exclude('resources/sass')
    ->exclude('storage')
    ->exclude('vendor')
;

$config = new PhpCsFixer\Config();
return $config
    ->registerCustomFixers(new \ErickSkrauch\PhpCsFixer\Fixers())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'braces_position' => [
            'classes_opening_brace'=>'same_line',
            'control_structures_opening_brace' => 'same_line',
            'functions_opening_brace'=>'same_line',
        ],
        'class_attributes_separation' => [
            'elements'=>[
                'const' => 'only_if_meta',
                'method' => 'one',
                'property' => 'only_if_meta',
                'trait_import' => 'none',
                'case' => 'none'
            ]
        ],
        'class_definition' => [
            'inline_constructor_arguments' => true,
            'space_before_parenthesis' => false,
            'single_line' => true,
            'single_item_single_line' => true,
        ],
        'empty_loop_body' => ['style' => 'braces'],
        'function_declaration' => [
            'closure_fn_spacing'=>'none',
            'closure_function_spacing'=>'none',
        ],
        'magic_constant_casing' => true,
        'method_argument_space' => [
            'on_multiline' => 'ignore'
        ],
        'new_with_parentheses' => [
            'anonymous_class'=>false
        ],
        'no_blank_lines_after_class_opening' => false,
        'no_extra_blank_lines' => [
            'tokens' => ['break', 'case', 'curly_brace_block', 'continue', 'default', 'extra', 'parenthesis_brace_block', 'switch', 'throw'],
        ],
        'no_unused_imports' => true,
        'semicolon_after_instruction' => true,
        'single_line_throw' => true,
        'single_space_around_construct' => [
            'constructs_followed_by_a_single_space' => ['abstract', 'as', 'case', 'class', 'const_import', 'do', 'else', 'elseif', 'final', 'finally', 'for', 'foreach', 'function', 'function_import', 'insteadof', 'interface', 'namespace', 'new', 'private', 'protected', 'public', 'static', 'trait', 'try', 'use', 'use_lambda']
        ],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays']
        ],
        'visibility_required' => [
            'elements' => ['property', 'method']
        ],
        'ErickSkrauch/blank_line_around_class_body'=>true,
    ])
    ->setFinder($finder);

<?php

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->in(__DIR__)
    ->notPath('_ide_helper.php')
    ->exclude('bootstrap/cache')
    ->exclude('database/snapshots')
    ->exclude('docs')
    ->exclude('public')
    ->exclude('node_modules')
    ->exclude('resources/css')
    ->exclude('resources/js')
    ->exclude('resources/sass')
    ->exclude('storage')
    ->exclude('tests/Browser/console')
    ->exclude('tests/Browser/screenshots')
    ->exclude('vendor')
;

$config = new PhpCsFixer\Config();
return $config
    ->registerCustomFixers(new Ely\CS\Fixers())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'braces'=>[
            'allow_single_line_closure'=>true,
            'position_after_functions_and_oop_constructs'=>'same',
        ],
        'class_definition'=>[
            'inline_constructor_arguments' => true,
            'space_before_parenthesis'=>false,
            'single_line'=>true,
            'single_item_single_line'=>true,
        ],
        'curly_braces_position'=>[
            'classes_opening_brace'=>'same_line',
            'control_structures_opening_brace' => 'same_line',
            'functions_opening_brace'=>'same_line',
        ],
        'empty_loop_body'=>['style'=>'braces'],
        'function_declaration'=>[
            'closure_fn_spacing'=>'none',
            'closure_function_spacing'=>'none',
        ],
        'magic_constant_casing'=>true,
        'method_argument_space'=>[
            'on_multiline'=>'ignore'
        ],
        'no_blank_lines_after_class_opening'=>false,
        'no_extra_blank_lines'=>[
            'tokens' => [
                'break',
                'case',
                'curly_brace_block',
                'continue',
                'default',
                'extra',
                'parenthesis_brace_block',
                'switch',
                'throw',
            ],
        ],
        'no_unused_imports'=>true,
        'semicolon_after_instruction'=>true,
        'single_line_throw'=>true,
        'visibility_required'=>[
            'elements'=>['property', 'method']
        ],

        'Ely/blank_line_around_class_body'=>true
    ])
    ->setFinder($finder);

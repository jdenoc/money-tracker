<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

/**
 * Class TraitMakeCommand
 *
 * @package App\Console\Commands
 *
 * Instructions for this command came from here:
 * https://medium.com/cedex-technologies-llp/laravel-5-5-trait-generator-b83202a72294
 */
class TraitMakeCommand extends GeneratorCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:trait';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Trait';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/trait.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Traits';
    }

}

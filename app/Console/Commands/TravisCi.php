<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TravisCi extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "travis-ci
                            {--display-env : output environment variables and values}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Outputs details needed for travis-ci builds';

    /**
     * Create a new command instance.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(){
        foreach($this->options() as $command_option){
            switch($command_option){
                case 'display-env':
                    $this->info("Environment Variables:");
                    $env_values = array_map([$this, 'output_environment_variable_and_value'], array_keys($_ENV));
                    $this->table(['variable', 'value'], $env_values);
                    $this->line('');    // new line after output in case we have other output
                    break;
            }
        }
    }

    private function output_environment_variable_and_value($variable_name){
        return ['variable'=>$variable_name, 'value'=>env($variable_name)];
    }

}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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
     * Execute the console command.
     */
    public function handle(){
        foreach($this->options() as $command_option){
            switch($command_option){
                case 'display-env':
                    $this->comment("Environment Variables:");
                    $env_names = array_merge(array_keys($_ENV), $this->get_env_names_from_dotenv());
                    $env_names = array_unique($env_names);
                    $env_values = array_map([$this, 'output_environment_variable_and_value'], $env_names);
                    $this->table(['variable', 'value'], $env_values);
                    $this->line('');    // new line after output in case we have other output
                    break;
            }
        }
    }

    /**
     * @param string $variable_name
     * @return array
     */
    private function output_environment_variable_and_value($variable_name){
        return ['variable'=>$variable_name, 'value'=>env($variable_name)];
    }

    /**
     * @return array
     */
    private function get_env_names_from_dotenv(){
        $dot_env_file_path = rtrim(app()->environmentPath(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.app()->environmentFile();
        $dot_env_variable_names = [];
        if(File::exists($dot_env_file_path)){
            try{
                $dot_env_contents = File::get($dot_env_file_path);
                $dot_env_content_lines = explode("\n", $dot_env_contents);
                foreach($dot_env_content_lines as $dot_env_content_line){
                    if(!empty($dot_env_content_line) && strpos($dot_env_content_line, '#') !== 0){
                        $dot_env_variable_names[] = substr($dot_env_content_line, 0, strpos($dot_env_content_line, '='));
                    }
                }
            } catch (\Exception $e){
                $this->alert(".env does not exist");
            }
        }
        return $dot_env_variable_names;
    }

}
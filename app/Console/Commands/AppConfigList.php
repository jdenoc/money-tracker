<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AppConfigList extends Command {

    const OPTION_ONLY_CONFIG = 'only-config';
    const OPTION_ONLY_ENV = 'only-env';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:config-list
                            {--only-env : only output environment variables and values}
                            {--only-config : only output config variables and values}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Outputs environment and config variable values';

    /**
     * Execute the console command.
     */
    public function handle():int{
        $has_options = $this->hasOption(self::OPTION_ONLY_CONFIG) || $this->hasOption(self::OPTION_ONLY_ENV);

        if(($has_options && $this->hasOption(self::OPTION_ONLY_CONFIG)) || !$has_options) {
            // as only as we're
            $config = config()->all();
            $parsed_config = $this->parseConfigValues($config);
            $this->table(['variable', 'value'], $parsed_config);
            $this->line('');    // new line after output in case we have other output
        }

        if (($has_options && $this->hasOption(self::OPTION_ONLY_ENV)) || !$has_options) {
            $this->comment("Environment Variables:");
            $dot_env_file_path = app()->environmentFilePath();
            $env_names = array_merge(array_keys($_ENV), $this->get_env_names_from_dotenv($dot_env_file_path));
            $env_names = array_unique($env_names);
            $env_values = array_map([$this, 'output_environment_variable_and_value'], $env_names);
            $env_values[] = ['.env file path', $dot_env_file_path];
            $this->table(['variable', 'value'], $env_values);
            $this->line('');    // new line after output in case we have other output
        }

        return 0;
    }

    /**
     * @param array $config
     * @param string $config_name_current_level
     * @param array $config_table
     * @return array
     */
    private function parseConfigValues(array $config, string $config_name_current_level = '', array $config_table = []): array{
        foreach ($config as $config_name => $config_value) {
            if (!empty($config_name_current_level)) {
                $config_name = $config_name_current_level . '.' . $config_name;
            }
            if (is_array($config_value)) {
                // recursive stuff here
                $config_table = $this->parseConfigValues($config_value, $config_name, $config_table);
            } else {
                $config_table[] = ['variable' => $config_name, 'value' => $config_value];
            }
        }

        return $config_table;
    }

    /**
     * @param string $variable_name
     * @return array
     */
    private function output_environment_variable_and_value(string $variable_name): array{
        return ['variable' => $variable_name, 'value' => env($variable_name)];
    }

    /**
     * @param string $dot_env_file_path path to .env file
     * @return array
     */
    private function get_env_names_from_dotenv(string $dot_env_file_path): array{
        $dot_env_variable_names = [];
        if (File::exists($dot_env_file_path)) {
            try {
                $dot_env_contents = File::get($dot_env_file_path);
                $dot_env_content_lines = explode("\n", $dot_env_contents);
                foreach ($dot_env_content_lines as $dot_env_content_line) {
                    if (!empty($dot_env_content_line) && strpos($dot_env_content_line, '#') !== 0) {
                        $dot_env_variable_names[] = substr($dot_env_content_line, 0, strpos($dot_env_content_line, '='));
                    }
                }
            } catch (\Exception $e) {
                $this->alert(".env does not exist");
            }
        }
        return $dot_env_variable_names;
    }

}
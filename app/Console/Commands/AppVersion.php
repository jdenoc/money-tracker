<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AppVersion extends Command {

    const ENV_PARAM = "APP_VERSION";
    const CONFIG_PARAM = "app.version";
    const ARG_NAME = "version";
    const INFO_STRING_GET_VERSION = "Application version: %s";
    const INFO_STRING_SET_VERSION = "Application version [%s] has been set successfully.";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:version
                            {version? : The applications new version number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get/Set application version';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        // make sure .env file exists
        if(!File::exists(base_path('.env'))){
            $this->error(".env file does not exist.\nPlease create one before trying again.");
            return;
        }

        $version = $this->argument(self::ARG_NAME);
        if(empty($version)){
            $current_version = config(self::CONFIG_PARAM);  // getting config value from memory
            $current_version = empty($current_version) ? 'NOT YET SET' : $current_version;
            $this->info(sprintf(self::INFO_STRING_GET_VERSION, $current_version));
            return;
        }

        $this->writeNewEnvironmentFileWith($version);
        config([self::CONFIG_PARAM=>$version]);  // setting the config value in memory
        $this->info(sprintf(self::INFO_STRING_SET_VERSION, $version));
        return;
    }

    /**
     * Write a new environment file with the given key.
     *     Taken & modified from Illuminate\Foundation\Console\KeyGenerateCommand
     *
     * @param  string  $version
     * @return void
     */
    protected function writeNewEnvironmentFileWith($version){
        file_put_contents(base_path('.env'), preg_replace(
            $this->versionReplacementPattern(),
            self::ENV_PARAM.'='.$version,
            file_get_contents(base_path('.env'))
        ));
    }

    /**
     * Get a regex pattern that will match env APP_VERSION with any random key.
     *     Taken & modified from Illuminate\Foundation\Console\KeyGenerateCommand
     *
     * @return string
     */
    protected function versionReplacementPattern(){
        $escaped = preg_quote('='.config(self::CONFIG_PARAM), '/');
        return "/^".self::ENV_PARAM."{$escaped}/m";
    }
}

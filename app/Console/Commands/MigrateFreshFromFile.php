<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Schema;

class MigrateFreshFromFile extends Command {

    const ARG_FILE = 'file';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fresh-from-file
                            {'.self::ARG_FILE.' : File that you wish to re-build database with.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drops all tables and replaces them with the contents of a provided file.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle():int{
        $file = $this->argument(self::ARG_FILE);

        // confirm file exists
        if(!file_exists($file)){
            $this->error('File does not exist');
            return 1;
        }

        // clear existing database
        Schema::dropAllTables();
        $this->info('Dropped all tables successfully.');

        // import file contents into database
        DB::unprepared(file_get_contents($file));
        $this->info('Database file ['.$file.'] imported successfullly.');

        return 0;
    }

}
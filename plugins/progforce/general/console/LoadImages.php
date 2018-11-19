<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Classes\Helpers\GoogleDriveHelper;

class LoadImages extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'progforce:load-images';

    /**
     * @var string The console command description.
     */
    protected $description = 'Loading image names into DB from GoogleDrive';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Getting images from Google Drive & writing it`s names to DB');
        GoogleDriveHelper::loadImagesToDB();
        $this->output->writeln('Done!');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}

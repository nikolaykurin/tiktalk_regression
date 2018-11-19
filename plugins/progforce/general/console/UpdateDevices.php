<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Models\RegisteredDevice;

class UpdateDevices extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'progforce:update-devices';

    /**
     * @var string The console command description.
     */
    protected $description = 'Updates Devices';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Started....');
        $devices = RegisteredDevice::get();
        foreach ($devices as $device) {
            $device->mixed_id = $device->device_id;
            $device->save();
        }
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

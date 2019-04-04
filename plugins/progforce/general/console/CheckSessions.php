<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Models\Session;

class CheckSessions extends Command {

    protected $name = 'progforce:check-sessions';

    protected $description = 'Check sessions and finish lost';

    public function handle() {
        foreach (Session::whereNull('datetime_end')->get() as $session) {
            if ($session->logs->count() > 0) {
                $session->datetime_end = $session->logs->first()->datetime->addSeconds(Session::$TTL)->format('Y-m-d H:i:s');
                $session->save();
            }
        }
    }

}

<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Models\TreatmentPlanPhase;

class TransferPhases extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'progforce:transfer-phases';

    /**
     * @var string The console command description.
     */
    protected $description = 'Transfers old phases to new structure';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        TreatmentPlanPhase::truncate();
        $plans = \Progforce\General\Models\PatientTreatmentPlan::get();
        foreach ($plans as $plan) {
            $rows = [];
            for ( $i=1; $i <= 20; $i++) {
                $fld = sprintf('treatment_phase_%02d_id', $i);
                $phaseId = $plan->$fld;
                if ($phaseId) {
                    $fld = sprintf('treatment_status_%02d_id', $i);
                    $statusId = $plan->$fld;
                    $rows[] = [
                        'plan_id' => $plan->id,
                        'row_num' => $plan->id,
                        'phase_id' => $phaseId,
                        'phase_status_id' => $statusId ? $statusId : 0
                    ];
                }
            }
            if ($rows) {
                TreatmentPlanPhase::insert($rows);
            }
        }
        $this->output->writeln('Done!');
    }


}

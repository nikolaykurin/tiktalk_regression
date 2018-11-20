<?php namespace Progforce\General\Controllers\RoutesControllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Progforce\General\Classes\Helpers\CountyHelper;
use Progforce\General\Classes\Helpers\ResultHelper;
use Progforce\General\Models\Result;
use Progforce\User\Models\User;
use Genderize;
use Exception;

class Results extends Controller {

    static $ITERATIONS_COUNT = 10000;
    static $FIELDS = [ 'patient_age', 'patient_gender', 'treatment_complexity', 'treatment_phases_count', 'treatment_duration' ];

    static $SPACE_SEPARATOR = ' ';

    public function generate() {
        for ($iterator = 0; $iterator < self::$ITERATIONS_COUNT; $iterator++ ) {
            Result::generate()->save();
        }

        return response('OK');
    }

    // TODO: split this on: getData - collectData - modifyData - complementWithRandomData
    /**
     * If PATIENT_GENDER can't be get - it will be average between genders;
     * If PATIENT_AGE can't be get - it will be average;
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function fill(Request $request) {
        $genders = array_flip(ResultHelper::$GENDERS);

        // protocol_status = 3 - finished plans
        $db_data = User::whereHas('patient_treatment_plan', function ($query) {
                $query->where('protocol_status', 3);
            })->with([
            'patient_treatment_plan' => function ($query) {
                $query->where('protocol_status', 3)->with([
                    'phases' => function ($query) {
                        $query->get([ 'complexity_id', 'phase_status_date' ]);
                    }
                ])->get([ 'id', 'user_id', 'created_at' ]);
            },
            'country' => function ($query) {
                $query->get([ 'id', 'description' ]);
            }
        ])->get([ 'id', 'birth_date', 'first_name', 'country_id' ])->toArray();

        $data = [];
        $patient_age_sum = 0;
        foreach ($db_data as $datum) {
            foreach ($datum['patient_treatment_plan'] as $item) {
                $row = [];

                $gender = null;
                try {
                    $gender = Genderize::name($datum['first_name'])->country(CountyHelper::normalize($datum['country']['description']))->get()->result[0]->gender;
                } catch (Exception $exception) {
                    //
                }

                $row['patient_age'] = $datum['birth_date'] ? Carbon::createFromFormat('Y-m-d', $datum['birth_date'])->diffInYears() : null;
                $row['patient_gender'] = is_null($gender) ? 1.5 : $genders[$gender];

                $treatment_phases_count = 0;
                $treatment_complexities_sum = 0;
                $treatment_finished_at = null;
                foreach ($item['phases'] as $phase) {
                    if (is_null($phase['phase_status_date'])) {
                        continue;
                    }

                    $treatment_complexities_sum += $phase['complexity_id'] < 3 ? $phase['complexity_id'] : 1.5;
                    if (is_null($treatment_finished_at) || Carbon::createFromFormat('Y-m-d', $treatment_finished_at)->lessThan(Carbon::createFromFormat('Y-m-d', $phase['phase_status_date']))) {
                        $treatment_finished_at = $phase['phase_status_date'];
                    }

                    $treatment_phases_count++;
                }

                $row['treatment_duration'] = Carbon::createFromFormat('Y-m-d H:i:s', $item['created_at'])->diffInDays(Carbon::createFromFormat('Y-m-d', $treatment_finished_at));
                $row['treatment_phases_count'] = $treatment_phases_count;
                $row['treatment_complexity'] = $treatment_complexities_sum / $row['treatment_phases_count'];

                if (!is_null($row['patient_age'])) {
                    $patient_age_sum += $row['patient_age'];
                }

                $data[] = $row;
            }
        }

        $average_patient_age = (int) round($patient_age_sum / count($data));

        $data = array_map(function ($item) use ($average_patient_age) {
            if (!is_null($item['patient_age'])) {
                $item['patient_age'] = $average_patient_age;
            }

            return $item;
        }, $data);

        dd($data);

        return response('OK');
    }

    public function clear(Request $request) {
        Result::truncate();

        return response('ok');
    }

    public function get(Request $request) {
        $ended_only = $request->exists('ended_only');

        return $ended_only ?
            Result::whereNotNull('treatment_duration')->get(self::$FIELDS)->toArray() :
            Result::all(self::$FIELDS)->toArray();
    }

    public function make_txt(Request $request) {
        $data = Result::whereNotNull('treatment_duration')->get(self::$FIELDS)->toArray();

        $str = '';
        $keys = array_map(function ($item) {
            return '"' . $item . '"';
        }, array_keys($data[0]));

        $str .= implode(',', $keys) . PHP_EOL;

        for ($i = 1; $i < count($data); $i++) {
            $str .= implode(',', array_values($data[$i])) . PHP_EOL;
        }

        file_put_contents(ResultHelper::getDataFilePath(), $str);

        return response('ok');
    }

    public function build(Request $request) {
        $result = shell_exec(sprintf('Rscript %s', ResultHelper::getRBuildScriptPath()));

        return response()->json([
            'result' => $result
        ]);
    }

    public function predict(Request $request) {
        $param_patient_age = $request->input('patient_age');
        $param_patient_gender = $request->input('patient_gender');
        $treatment_complexity = $request->input('treatment_complexity');

        $result = shell_exec(sprintf('Rscript %s %s %s %s', ResultHelper::getRPredictScriptPath(), $param_patient_age, $param_patient_gender, $treatment_complexity));
        $exploded_result = explode(self::$SPACE_SEPARATOR, $result);

        return response()->json([
            'result' => ceil(trim(array_pop($exploded_result)))
        ]);
    }

}

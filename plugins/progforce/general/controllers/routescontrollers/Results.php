<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Progforce\General\Classes\Helpers\ResultHelper;
use Progforce\General\Models\Result;

class Results extends Controller {

    static $ITERATIONS_COUNT = 10000;
    static $FIELDS = [ 'patient_age', 'patient_gender', 'treatment_complexity', 'treatment_duration' ];

    static $SPACE_SEPARATOR = ' ';

    public function fill(Request $request) {
        for ($iterator = 0; $iterator < self::$ITERATIONS_COUNT; $iterator++ ) {
            Result::generate()->save();
        }

        return response('ok');
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

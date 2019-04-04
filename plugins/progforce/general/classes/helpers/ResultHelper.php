<?php namespace Progforce\General\Classes\Helpers;

use Carbon\Carbon;
use Progforce\General\Models\Result;
use Progforce\User\Models\User;
use Genderize;
use Exception;

class ResultHelper {

    public static $MIN_DATA_COUNT = 100;

    public static $MIN_ITERATIONS_COUNT = 1000;
    public static $MAX_ITERATIONS_COUNT = 10000;

    public static $SYSTEM_START_DATE = '2018-05-21 00:00:00';
    public static $SECONDS_IN_DAY = 86400;
    public static $SECONDS_IN_WEEK = 604800;

    public static $MIN_PATIENT_AGE = 4;
    public static $MAX_PATIENT_AGE = 18;

    public static $GENDERS = [
        1 => 'male',
        2 => 'female'
    ];

    public static $COMPLEXITIES = [
        1 => 'simple',
        2 => 'complex'
    ];

    public static function fill($result) {
        $result = self::addPatientAge($result);
        $result = self::addPatientGender($result);
        $result = self::addTreatmentPhasesCount($result);
        $result = self::addTreatmentComplexity($result);
        $result = self::addTreatmentStartedAt($result);
        $result = self::addTreatmentFinishedAt($result);
        $result = self::addTreatmentDuration($result);
        $result = self::addIsReal($result);

        return $result;
    }

    public static function addPatientAge($result) {
        $result->patient_age = rand(self::$MIN_PATIENT_AGE, self::$MAX_PATIENT_AGE);

        return $result;
    }

    public static function addPatientGender($result) {
        $min_value = min(array_keys(self::$GENDERS));
        $max_value = max(array_keys(self::$GENDERS));

        $result->patient_gender = rand($min_value, $max_value);

        return $result;
    }

    public static function addTreatmentPhasesCount($result) {
        $result->treatment_phases_count = rand(1, 10);

        return $result;
    }

    public static function addTreatmentComplexity($result) {
        $min_value = min(array_keys(self::$COMPLEXITIES));
        $max_value = max(array_keys(self::$COMPLEXITIES));

        $treatment_complexity_sum = 0;
        for ($i = 0; $i <= $result->treatment_phases_count; ++$i) {
            $treatment_complexity_sum += $result->patient_age >= (self::$MIN_PATIENT_AGE + self::$MAX_PATIENT_AGE) / 2 ?
                rand($min_value * rand(10, 15) / 10, $max_value) :
                rand($min_value, $max_value);
        }

        $result->treatment_complexity = $treatment_complexity_sum / $i;

        return $result;
    }

    public static function addTreatmentStartedAt($result) {
        $result->treatment_started_at = date('Y-m-d H:i:s', rand(strtotime(self::$SYSTEM_START_DATE), time()));

        return $result;
    }

    public static function addTreatmentFinishedAt($result) {
        $finished_at = $result->treatment_complexity >= (max(array_keys(self::$COMPLEXITIES)) + min(array_keys(self::$COMPLEXITIES))) / 2 ?
            Carbon::createFromFormat('Y-m-d H:i:s', $result->treatment_started_at)->addSeconds(rand(rand(1, 3) * self::$SECONDS_IN_WEEK, rand(5, 12) * self::$SECONDS_IN_WEEK) * $result['treatment_phases_count'] * rand(70, 110) / 100) :
            Carbon::createFromFormat('Y-m-d H:i:s', $result->treatment_started_at)->addSeconds(rand(self::$SECONDS_IN_DAY, rand(1, 5) * self::$SECONDS_IN_WEEK) * $result['treatment_phases_count'] * rand(50, 100) / 100);

        $result->treatment_finished_at = $finished_at->lessThan(Carbon::now()) ? $finished_at->format('Y-m-d H:i:s') : null;

        return $result;
    }

    public static function addTreatmentDuration($result) {
        $started_at = Carbon::createFromFormat('Y-m-d H:i:s', $result->treatment_started_at);
        $finished_at = $result->treatment_finished_at ? Carbon::createFromFormat('Y-m-d H:i:s', $result->treatment_finished_at) : null;

        $result->treatment_duration = $finished_at ? $finished_at->diffInDays($started_at) * rand(5, 30) : null;

        return $result;
    }

    public static function addIsReal($result) {
        $result->is_real = false;

        return $result;
    }

    public static function getRBuildScriptPath() {
        return implode(DIRECTORY_SEPARATOR, [
            base_path(),
            config('r.folder'),
            config('r.filenames.build')
        ]);
    }

    public static function getRPredictScriptPath() {
        return implode(DIRECTORY_SEPARATOR, [
            base_path(),
            config('r.folder'),
            config('r.filenames.predict')
        ]);
    }

    public static function getDataFilePath() {
        return config('r.paths.data_file');
    }

    public static function getModelPath() {
        return config('r.paths.model');
    }

    public static function getData() {
        return User::whereHas('sessions')
            ->with([
                'patient_treatment_plan' => function ($query) {
                    $query
                        ->whereHas('phases')
                        ->with([
                            'phases' => function ($query) {
                                $query->get([ 'complexity_id', 'phase_status_date' ]);
                            }
                        ])
                        ->get([ 'id', 'user_id', 'created_at' ]);
                },
                'country' => function ($query) {
                    $query
                        ->get([ 'id', 'description' ]);
                },
                'sessions' => function ($query) {
                    $query
                        ->get([ 'id', 'user_id', 'datetime_start', 'datetime_end' ]);
                }
            ])->get([ 'id', 'birth_date', 'first_name', 'country_id' ])->toArray();
    }

    public static function parseData($data) {
        $result = [];
        foreach ($data as $datum) {
            $treatment_duration = 0;

            foreach ($datum['sessions'] as $session) {
                $treatment_duration += $session['datetime_end'] ? Carbon::createFromFormat('Y-m-d H:i:s', $session['datetime_start'])->diffInMinutes(Carbon::createFromFormat('Y-m-d H:i:s', $session['datetime_end'])) : 0;
            }

            $avg_treatment_duration = $treatment_duration / count($datum['patient_treatment_plan']);

            foreach ($datum['patient_treatment_plan'] as $item) {
                $row = [];

                $gender = null;
                try {
//                    $gender = Genderize::name($datum['first_name'])->country(CountryHelper::normalize($datum['country']['description']))->get()->result[0]->gender;
                } catch (Exception $exception) {
                    //
                }

                $row['patient_age'] = $datum['birth_date'] ? Carbon::createFromFormat('Y-m-d', $datum['birth_date'])->diffInYears() : null;
                $row['patient_gender'] = is_null($gender) ? array_sum(array_keys(self::$GENDERS)) / count(self::$GENDERS) : array_flip(self::$GENDERS)[$gender];

                $treatment_phases_count = 0;
                $treatment_complexities_sum = 0;
                $treatment_finished_at = null;
                foreach ($item['phases'] as $phase) {

                    $treatment_complexities_sum += $phase['complexity_id'] < 3 ? $phase['complexity_id'] : 1.5;
                    if (!is_null($phase['phase_status_date']) && (is_null($treatment_finished_at) || Carbon::createFromFormat('Y-m-d', $treatment_finished_at)->lessThan(Carbon::createFromFormat('Y-m-d', $phase['phase_status_date'])))) {
                        $treatment_finished_at = $phase['phase_status_date'];
                    }

                    $treatment_phases_count++;
                }


                $row['treatment_started_at'] = $item['created_at'];
                $row['treatment_finished_at'] = $treatment_finished_at ? Carbon::createFromFormat('Y-m-d', $treatment_finished_at)->format('Y-m-d H:i:s') : null;

//                $treatment_duration = $row['treatment_finished_at'] ? Carbon::createFromFormat('Y-m-d H:i:s', $row['treatment_started_at'])->diffInDays(Carbon::createFromFormat('Y-m-d H:i:s', $row['treatment_finished_at'])) : null;

                $row['treatment_duration'] = $avg_treatment_duration === 0 ? 1 : $avg_treatment_duration;
                $row['treatment_phases_count'] = $treatment_phases_count;
                $row['treatment_complexity'] = $treatment_complexities_sum / $row['treatment_phases_count'];
                $row['is_real'] = true;

                $result[] = $row;
            }
        }

        return $result;
    }

    public static function completeData($data) {
        $patient_age_sum = 0;

        array_map(function ($item) use (&$patient_age_sum) {
            $patient_age_sum += $item['patient_age'];
        }, $data);

        $average_patient_age = (int) round($patient_age_sum / count($data));

        return array_map(function ($item) use ($average_patient_age) {
            if (!is_null($item['patient_age'])) {
                $item['patient_age'] = $average_patient_age;
            }

            return $item;
        }, $data);
    }

    public static function complementWithRandomData($data) {
        for ($iterator = 0; $iterator < rand(self::$MIN_ITERATIONS_COUNT, self::$MAX_ITERATIONS_COUNT); $iterator++ ) {
            $data[] = Result::generate()->toArray();
        }

        return $data;
    }
}

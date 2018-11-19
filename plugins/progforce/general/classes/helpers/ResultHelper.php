<?php namespace Progforce\General\Classes\Helpers;


use Carbon\Carbon;

class ResultHelper {

    public static $SYSTEM_START_DATE = '2018-09-21 00:00:00';
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
        $result = self::addTreatmentComplexity($result);
        $result = self::addTreatmentStartedAt($result);
        $result = self::addTreatmentFinishedAt($result);
        $result = self::addTreatmentDuration($result);

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

    public static function addTreatmentComplexity($result) {
        $min_value = min(array_keys(self::$COMPLEXITIES));
        $max_value = max(array_keys(self::$COMPLEXITIES));

        $result->treatment_complexity = $result->patient_age >= (self::$MIN_PATIENT_AGE + self::$MAX_PATIENT_AGE) / 2 ?
            rand($min_value, $max_value) :
            rand(1, 10) > 7 ? rand($min_value, $max_value) : 1;
        ;

        return $result;
    }

    public static function addTreatmentStartedAt($result) {
        $result->treatment_started_at = date('Y-m-d H:i:s', rand(strtotime(self::$SYSTEM_START_DATE), time()));

        return $result;
    }

    public static function addTreatmentFinishedAt($result) {
        $finished_at = $result->treatment_complexity === max(array_keys(self::$COMPLEXITIES)) ?
            Carbon::createFromFormat('Y-m-d H:i:s', $result->treatment_started_at)->addSeconds(rand(self::$SECONDS_IN_DAY, rand(1, 5) * self::$SECONDS_IN_WEEK)) :
            Carbon::createFromFormat('Y-m-d H:i:s', $result->treatment_started_at)->addSeconds(rand(rand(1, 3) * self::$SECONDS_IN_WEEK, rand(5, 12) * self::$SECONDS_IN_WEEK));

        $result->treatment_finished_at = $finished_at->lessThan(Carbon::now()) ? $finished_at->format('Y-m-d H:i:s') : null;

        return $result;
    }

    public static function addTreatmentDuration($result) {
        $started_at = Carbon::createFromFormat('Y-m-d H:i:s', $result->treatment_started_at);
        $finished_at = $result->treatment_finished_at ? Carbon::createFromFormat('Y-m-d H:i:s', $result->treatment_finished_at) : null;

        $result->treatment_duration = $finished_at ? $finished_at->diffInDays($started_at) : null;

        return $result;
    }

    public static function getRBuildScriptPath() {
        return implode(DIRECTORY_SEPARATOR, [
            base_path(),
            config('r.path'),
            config('r.filenames.build')
        ]);
    }

    public static function getRPredictScriptPath() {
        return implode(DIRECTORY_SEPARATOR, [
            base_path(),
            config('r.path'),
            config('r.filenames.predict')
        ]);
    }

    public static function getDataFilePath() {
        return config('r.paths.data');
    }

    public static function getModelPath() {
        return config('r.paths.model');
    }
}

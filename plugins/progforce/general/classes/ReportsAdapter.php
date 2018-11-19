<?php

namespace Progforce\General\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Classes\Helpers\WordsHelper;
use Progforce\General\Models\LanguageConfiguration;
use Progforce\General\Models\Report;
use Progforce\General\Models\TreatmentPlanPhase;

class ReportsAdapter {

    private $user;
    private $wordIds;
    private $reportId;
    private $dateRange;
    private $patientReports;

    public function __construct($user, $reportId, Array $dateRange) {
        $this->user = $user;
        $this->wordIds = [];
        $this->reportId = $reportId;
        $this->dateRange = $dateRange;
        $this->setPatientReports();
    }

    public function getProgressVals() {
        $res = ['sounds' => [0 => 'All'], 'vals' => []];
        $qry = TreatmentPlanPhase::from('progforce_general_treatment_plans_phases as pp')->
            select(
                'pp.phase_status_id',
                'p.sound_id',
                's.sound',
                DB::raw('count(*) as phase_count')
            )->
            leftJoin('progforce_general_patient_treatment_plans as p', 'p.id', '=', 'pp.plan_id')->
            leftJoin('progforce_general_treatment_phases as ph', 'ph.id', '=', 'pp.phase_id')->
            leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'p.sound_id')->
            where('p.user_id', $this->user->id);
        $phases = $qry->groupBy('pp.phase_status_id', 'p.sound_id', 's.sound')->get();

        $count = 0;
        $countCompleted = 0;
        foreach ($phases as $phase) {
            if (!key_exists($phase->sound_id, $res['vals'])) {
                $res['vals'][$phase->sound_id] = ['completed' => 0, 'total' => 0];
            }
            $res['sounds'][$phase->sound_id] = $phase->sound;
            if ($phase->phase_status_id == 3) {
                $countCompleted+= $phase->phase_count;
                $res['vals'][$phase->sound_id]['completed'] += $phase->phase_count;
            }
            $res['vals'][$phase->sound_id]['total'] += $phase->phase_count;
            $count += $phase->phase_count;
        }
        $res['vals'][0] = ['completed' => $countCompleted, 'total' => $count];
        return $res;
    }

    public function getReports($pageNum) {
        $path = PathHelper::getUserRelativeRecordingsPath($this->user->id);
        $res = ['path' => $path, 'pagination' => [], 'records' => []];

        $absPath = PathHelper::getUserAbsoluteRecordingsPath($this->user->id);
        $folders = array_reverse(glob($absPath . '/' . 'REC_*'));
        $curdir = getcwd();
        foreach ($folders as $folder) {
            if (!$this->isFolderInDateRange($folder)) { continue; }

            chdir($folder);
            $files = glob('*.{wav}', GLOB_BRACE);
            foreach ($files as $file) {
                $this->setWord($res, $folder, $file);
            }
        }
        chdir($curdir);

        $res['words'] = $this->getWords(array_unique($this->wordIds), $this->user->language_id);
        $this->setPagination($res, $pageNum);

        return $res;
    }

    private function setPagination(&$res, $pageNum) {
        $rowsPerPage = 10;
        $rowCount = count($res['records']);
        $pgCount = intval($rowCount/$rowsPerPage) + ($rowCount%$rowsPerPage > 0 ? 1 : 0);
        $res['pagination'] = compact('pageNum', 'pgCount');
        if ($pgCount > 1) {
            $start = ($pageNum-1)*$rowsPerPage;
            if ($this->reportId == 0) { 
                $res['records'] = array_slice($res['records'], $start, $rowsPerPage, true);
            } else {
                    $res['words'] = array_slice($res['words'], $start, $rowsPerPage, true);
            }
        }
    }

    private function setPatientReports() {
        $qry = Report::where('user_id', $this->user->id);
        if ($this->dateRange) {
            $qry = $qry->whereBetween('created_at', $this->dateRange);
        }
        $reports = $qry->get();
        $this->patientReports = [];
        foreach ($reports as $report) {
            @$this->patientReports[$report->folder_name][$report->file_name] = [
                'gameId' => $report->game_id,
                'resultId' => $report->result_id
            ];
        }
    }

    private function isFolderInDateRange($folder) {
        if (!$this->dateRange) {
            return true;
        }

        $vals = explode('_', basename($folder));
        $folderDate = new Carbon($vals[2] . ' ' . $vals[3]);
        return $folderDate->between(
                        Carbon::parse($this->dateRange[0]), Carbon::parse($this->dateRange[1])
        );
    }

    private function setWord(&$res, $folder, $file) {
        $fileName =  basename($file, '.wav');
        $folderName = basename($folder);

        $fVals = explode('_', $folderName);
        $fileDate = array_get($fVals, 2, '') . ' ' . array_get($fVals, 3, '');

        $wordId = explode('_', $fileName, 2)[0];
        $this->wordIds[] = $wordId;
        $key = $folderName . '.' . $fileName;

        $gameId = array_get($this->patientReports, $key . '.gameId', 0);
        $game = array_get(\Config::get('tiktalk.games'), $gameId . '.name', '');

        $resultId = array_get($this->patientReports, $key . '.resultId', 0);
        if ($this->reportId == 0) {
            @$res['records'][$folderName][] = compact(
                    'wordId', 'file', 'fileDate', 'game', 'resultId'
            );
        } else {
            @$res['records'][$wordId][] = compact(
                    'folderName', 'file', 'fileDate', 'game', 'resultId'
            );
        }
    }

    private function getWords($wordIds, $langId) {
        $langCode = LanguageConfiguration:: getLangCode($langId);
        $words = WordsHelper::getWordsListByLang($wordIds, $langCode)->
                keyBy('word_id')->
                toArray();

        array_walk($words, function(&$value) use($langCode) {
            $value['imagePath'] = PathHelper::getWordImagePath($langCode, $value['word_id']);
        });
        return $words;
    }

}

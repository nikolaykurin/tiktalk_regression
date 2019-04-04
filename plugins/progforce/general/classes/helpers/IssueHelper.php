<?php namespace Progforce\General\Classes\Helpers;

use Progforce\General\Models\IssueCodes;
use Illuminate\Support\Facades\Log;

class IssueHelper
{

    const ISSUE_NO_TREATMENT_PLAN = 1005;
    const ISSUE_USER_NOT_FOUND = 1004;
    const ISSUE_AM_NOT_FOUND = 1003;
    const ISSUE_NOT_ENOUGH_WORDS_LEVEL = 1002;
    const ISSUE_NOT_ENOUGH_WORDS_AM = 1001;

    /**
     * @param $issueCode
     * @param array $logData
     * @return array
     */
    public static function getIssue($issueCode, $logData = []) {
        $issue = IssueCodes::getIssueByCode($issueCode);

        if (!empty($logData)) {
            Log::debug(sprintf('%s [%s]', $issue->message, json_encode($logData)));
        }

        return [
            'success' => false,
            'error_code' => $issue->issue_id,
            'message' => $issue->message,
            'description' => $issue->description
        ];
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Manual Date-Range Report Limits
    |--------------------------------------------------------------------------
    |
    | Maximum number of inclusive calendar days an Admin may select when
    | generating a manual date-range visitor Excel report.
    |
    */

    'max_range_days' => max(2, (int) env('REPORT_MAX_RANGE_DAYS', 31)),

    /*
    |--------------------------------------------------------------------------
    | Report Day Coverage (Asia/Manila)
    |--------------------------------------------------------------------------
    |
    | Daily and date-range reports include ALL visitor records for each
    | complete calendar day: 00:00:00 through 23:59:59 Asia/Manila.
    | There is no early-morning cutoff.
    |
    */

];

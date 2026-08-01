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
    | Operating Hours (Asia/Manila)
    |--------------------------------------------------------------------------
    |
    | Visitor monitoring operations are reported from 06:00:00 through
    | 23:59:59 on each selected calendar day. Records between midnight and
    | 05:59:59 are excluded for every included date.
    |
    */

    'operating_hour_start' => 6,
    'operating_minute_start' => 0,
    'operating_second_start' => 0,

];

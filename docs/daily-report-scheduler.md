# Daily report generation and deployment

## Confirmed source-code causes

Before this fix, both `DailyReportController::index()` and
`OfficeDailyReportController::index()` called a ten-minute-throttled page catch-up
routine. The routines called `ensureMissingDailyReports(7, null)` and
`ensureMissingOfficeDailyReports(..., 7, null)`. Each service routine started at
yesterday and generated every missing or failed report over the last seven
complete Manila calendar days. On October 12, that is October 5–11. Deleting
database report rows makes them missing again; the next eligible GET generates
them again at the time of the request. The cache entries were throttles, not
cached report lists.

`routes/console.php` also scheduled seven-day Admin catch-up every hour and office
catch-up at minute 15 of every hour. These provide another way for deleted rows
to return. Admin's primary task ran at 00:01 instead of 00:00. The 18:14–18:15
timestamps match the available page/catch-up paths; attributing a specific
production run requires the production request and scheduler logs.

Read-only inspection of the database configured in this workspace found exactly
7 Admin reports for October 5–11, created October 12 at 18:14:42–18:14:43, and
7 reports for each of 10 office scopes, created at 18:15:04–18:15:10. Generating
all office scopes in sequence at minute 15 strongly points to the registered
office hourly catch-up command; one office page request only generates that
office's scope. Admin's 18:14 timing is consistent with page catch-up. These are
inferences from the source and database timestamps; the Hostinger job list and
production access/scheduler logs were not available in this workspace.

The configured application timezone is Asia/Manila, the database is PostgreSQL,
and the cache and queue drivers are database. `jobs` and `failed_jobs` were both
empty at inspection time, so there was no queued-report backlog then. Report
timestamps, visit `entry_time`, and office `scan_time` are all `timestamp without
time zone`; the PostgreSQL session is UTC, which does not shift those naive
columns. The new uniqueness migration was initially pending. It was subsequently
applied to this configured Supabase database at the user's request and verified
as migration batch 19. All 77 existing report records remained intact.
Local private storage contained 38 daily and 1 date-range file; these are local
filesystem counts and do not establish the Hostinger file counts.

The Daily Report Download GET endpoints also regenerated completed reports when
their files were missing. All those implicit generation paths are removed.
No report jobs, login triggers, dashboard triggers, API generation endpoints,
component-mount generation, or filesystem-to-database import were found in the
checked-in application. `AppServiceProvider` does not initialize reports.

Excel files are separate from report rows under `storage/app/private/reports/daily`
(and `reports/date-range`). Removing report rows does not remove these files, but
the list queries only the database and never imports files. No cleanup is needed
to keep an empty list empty. This fix deletes no production records or files.

## Behavior after the fix

- GET list, refresh, pagination, date filters and Download only read reports.
  Download still records its existing audit event. A missing Excel file prompts
  the user to use the explicit Regenerate action.
- Authorized POST Generate and confirmed POST Regenerate retain their routes and
  report formats. Older missing dates require an explicitly selected date.
- Admin `daily_visitor` and office `daily_visitor_office` records remain separate;
  an office only lists/downloads/regenerates its own records.
- The two scheduler entries run at `00:00 Asia/Manila` and capture yesterday once.
  Each active office gets its own report, including Admissions, even with no
  logged-in staff. No history scan, retry schedule, or seven-day backfill remains.
- A bare generation command outside the 00:00 minute exits without generation.
  `--force` requires an explicit date. Removed `--catch-up` flags are rejected;
  an old cron using that flag cannot silently backfill.
- Completed records are returned unchanged on repeat generation, including when
  their Excel files are missing. Explicit Regenerate is required to replace them.
- Per-date/per-scope service locks cover both HTTP and CLI generation. Scheduler
  locks and `onOneServer()` provide additional protection. Lock lifetimes are
  60 minutes; generation must complete within that period. Use the same persistent
  cache store for web and CLI processes; use a shared database/Redis store if there
  is more than one application server. Do not use `array`, `null`, or a failover to
  an in-process store in production.
- A new generated-column unique index enforces one daily report per date/type/
  scope, including Admin rows whose `office_id` is NULL. It leaves repeated
  date-range reports allowed. The earlier date-range migration had removed the
  original date/type unique constraint. The new migration checks for duplicates
  and stops with a review message rather than deleting or choosing records.
- Report visitor windows remain 00:00:00–23:59:59 Asia/Manila; Admin selects visit
  entry times and offices select scans at the scoped office. No workbook columns,
  styling, filename conventions, or date-range calculations were changed.

Midnight means the scheduler starts during that minute, not that Excel writing
finishes at exactly 00:00:00. The tasks run in the background so Admin writing
does not delay office task startup. Hostinger must permit the CLI child processes.
A scheduler outage at midnight leaves that date missing; no automatic catch-up
will occur later. An authorized user must request it manually.

## Deployment

Deploy the changed controllers, service, commands, views, `routes/console.php`,
and new migration together. Set the production environment:

```dotenv
APP_TIMEZONE=Asia/Manila
CACHE_STORE=database
```

`config/app.php` already defaults to `Asia/Manila`. Verify the resolved production
configuration after deployment; an environment override or old configuration
cache can still differ. Ensure the existing `cache`/`cache_locks` tables exist
when using the database cache driver. PHP CLI must satisfy `composer.lock` and
have the spreadsheet extensions, including zip, XML, mbstring, and GD.

From the Laravel project directory, using the same PHP executable as cron:

```sh
php artisan reports:inspect-daily
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:clear
php artisan list
php artisan schedule:list
```

Inspect the new migration before running it and review other pending migrations.
If duplicate daily rows exist, migration refuses to proceed without an explicit
review. It does not alter visitors, visits, office scans, or report filenames.
Do not use `migrate:fresh`, truncate visitor tables, or broadly delete queue/cache
records. Do not run scheduled tasks directly to test production daytime behavior
with an explicit date: that would be a manual generation request.

`schedule:list` must show exactly these report entries:

```text
0 0 * * *  generate:daily-visitor-report
0 0 * * *  generate:office-daily-visitor-reports
```

Both entries set `timezone('Asia/Manila')` explicitly in `routes/console.php`.
Laravel discovers classes in `app/Console/Commands`; `bootstrap/app.php` registers
`routes/console.php`. No legacy Console Kernel registration is needed here.

## Hostinger cron

Keep one scheduler cron, every minute (`* * * * *`). In hPanel Cron Jobs use a
Custom command with an absolute path to the project's `artisan`, for example:

```sh
/usr/bin/php /home/uXXXX/domains/example.com/nu-secure/artisan schedule:run
```

Replace both paths with the real paths and PHP version on the hosting account;
`/usr/bin/php` is an example, not a verified account-specific binary. Set Minute,
Hour, Day, Month and Weekday all to `*`. Hostinger schedules cron in UTC; running
every minute avoids a manual timezone offset, while Laravel selects Manila
midnight (16:00 UTC the previous date). Do not add direct report-generation cron
entries alongside this scheduler entry.

For a shell crontab rather than hPanel, the equivalent is:

```cron
* * * * * /usr/bin/php /absolute/project/artisan schedule:run >> /absolute/project/storage/logs/scheduler-cron.log 2>&1
```

If hPanel rejects shell redirection/special characters, place that command in a
shell script and schedule `/bin/sh /absolute/path/run-scheduler.sh`, following
Hostinger's special-character guidance. Keep script content as Unix LF lines.

Sources: [Laravel scheduler documentation](https://laravel.com/framework/docs/scheduling),
[Hostinger cron configuration](https://www.hostinger.com/support/1583465-how-to-set-up-a-cron-job-at-hostinger/),
[Hostinger shell command guidance](https://www.hostinger.com/support/5646919-how-to-set-up-a-cron-job-with-special-characters-at-hostinger/).

## Production investigation and acceptance

Run `php artisan reports:inspect-daily` on the actual deployed release. It only
reads metadata, counts queue payloads mentioning reports (candidates, not proof),
and counts private report files. Compare its database configuration and results
with the database from which rows were deleted. It prints raw stored timestamp
values; interpret them using the database column/session timezone. For PostgreSQL,
`SHOW TIMEZONE` and `information_schema.columns` distinguish `timestamp` from
`timestamptz`. Check the visitor timestamp convention too: the existing services
use Manila wall-clock window strings, so naive visitor timestamps must represent
Manila local time. Changing a visitor storage convention is a separate migration.

Check production `storage/logs/laravel.log`,
`daily-visitor-report-scheduler.log`, `office-daily-visitor-report-scheduler.log`,
web access logs, and the report Activity Log around the unexpected timestamps.
Old `*_page_catchup_failed`, `*_catchup_failed`, automatic generation events and
requests to `/admin/daily-reports` or `/office/daily-reports` identify candidates.
Both historical catch-up and scheduled generation logged system/null generators,
so `generated_by IS NULL` alone cannot distinguish their origin.

Inspect the hPanel List of Cron Jobs and `crontab -l` if available, plus any
supervisor/systemd `schedule:work` process. Remove only redundant report cron jobs
after confirming their purpose. Review queued/failed job classes before retrying
or removing specific obsolete report jobs; this repository has no report jobs,
but another deployed release could. Restart existing long-lived Laravel workers
as part of deployment if present. Do not retry all failed jobs to recover reports.
OPcache may require a hosting PHP process restart after the code deploy. Old
page-catch-up cache keys cannot generate anything once their code is removed;
broad `cache:clear` is unnecessary and can destroy live locks.

Acceptance example: on October 12, 2026 at 18:00 Manila, an empty report table
stays empty when either page is opened/refreshed/filtered. At October 13, 2026
00:00 Manila, only October 12 is generated once per Admin/active-office scope.
October 5–11 remain missing until explicitly requested. If testing a cleared
report table is necessary, use staging or obtain authorization to clear only the
report fixtures; production visitor data must remain untouched.

Local regression tests use in-memory SQLite and fake private storage. They cover
empty/deleted records, orphan files, read-only GETs, manual actions, scope
isolation, service locks, database uniqueness and real workbook/persistence paths
with visitor reads stubbed. No test connects to or resets the deployed database.

Manual server-operator examples (only when explicitly needed):

```sh
php artisan generate:daily-visitor-report 2026-10-05
php artisan generate:daily-visitor-report 2026-10-05 --force
php artisan generate:office-daily-visitor-reports 2026-10-05
```

The office CLI command targets all active offices for that one explicit date;
Admissions staff should use their authorized office POST Generate action to
generate only their own scope. Restrict command access to authorized operators.

# Office QR scanner adjustments

The existing Office scanner and shared Visitor Verification modal now support office adjustments. Camera, hardware, and manual scans continue through `OfficeScan.verify()` and `OfficeScanService::checkIn()`.

## Workflows

**Add This Office:** An unregistered office scan shows the existing warning and visitor details, with two actions in the modal footer. Add This Office opens an expandable form inside the same scrollable modal. The assigned office is read-only, the reason is required (255 characters maximum), and automatic check-in is checked by default. Confirmation appends an expectation, stores the reason and authenticated user, and optionally uses the existing check-in service. Without automatic check-in, no successful scan is created; the visitor appears in Expected Visitors and can scan the same QR later.

Additional offices are independent stops appended after the original route. Original enrollee step numbers and progress are preserved. Additional stops can be checked in without completing earlier enrollment offices and do not complete enrollment steps themselves.

**Mark as Wrong Office:** The same modal displays Log Wrong Office Note, with the assigned office and expected destination read-only. Required remarks (500 characters maximum) are appended to the original failed scan's existing `remarks` field. The scan remains unauthorized, the original diagnostic text is retained, and no expectation or successful scan is created. Repeating the same submission does not append the note or audit event again.

**Guard:** Open an active visitor's details from the Guard Dashboard. The Office Adjustments section allows a Guard to choose an additional active office and provide a reason. This updates the route without recording arrival at the office. The same section shows adjustment history, responsible users, reasons, and timestamps.

Office Staff cannot select another office; both the middleware assignment and service authorization enforce this. Cancelled, completed, checked-out, and other inactive visits are rejected. Additions lock the visit row and reject existing destinations. Office addition and optional check-in are one transaction; a check-in failure rolls the addition back. Required adjustment audit failures also prevent a successful adjustment.

Wrong-office alerts and notifications remain in place. Neither action resolves or deletes security records. Guards continue resolving alerts through the existing alert workflow with resolution notes.

## Database

The configured PostgreSQL schema was inspected before implementation. Migration:

`database/migrations/2026_10_19_000000_add_office_adjustment_details_to_office_expectation.php`

It adds nullable `office_expectation.added_by_user_id` (integer foreign key to `users.user_id`, null on deletion) and `addition_reason` (text). It does not add a remarks column or another audit table. `activity_logs` records the office, visit, scan, reason, user, check-in choice, and timestamp.

This migration was applied successfully to the configured PostgreSQL database on October 19, 2026. For other deployments:

```powershell
php artisan migrate --path=database/migrations/2026_10_19_000000_add_office_adjustment_details_to_office_expectation.php
```

Its PostgreSQL SQL preview and migration up/down against an isolated SQLite test database have been verified.

## Files

Modified:

- `app/Http/Controllers/GuardDashboardController.php`
- `app/Http/Controllers/Office/OfficeScannerController.php`
- `app/Services/OfficeScanService.php`
- `app/Services/OfficeVisitorQueryService.php`
- `resources/views/guard/dashboard.blade.php`
- `resources/views/office/components/scan-result-modal.blade.php`
- `resources/views/office/partials/scan-scripts.blade.php`
- `resources/views/office/scanner.blade.php`
- `routes/web.php`

Created:

- `app/Http/Controllers/GuardOfficeAdjustmentController.php`
- `database/migrations/2026_10_19_000000_add_office_adjustment_details_to_office_expectation.php`
- `tests/Feature/OfficeAdjustmentTest.php`
- `tests/frontend/office-adjustments.test.mjs`
- `docs/office-adjustments.md`

The earlier image-upload change in `resources/views/guard/register.blade.php` is separate from this feature.

## Verification

```powershell
php artisan test --compact --filter='OfficeAdjustmentTest|OfficeDashboardTest|OfficeDashboardLoadingTest|GuardActiveAlertsTest'
node --test tests/frontend/office-adjustments.test.mjs
php artisan view:cache
```

The new PHP feature tests cover correct-office scans, adding with and without check-in, wrong-office remarks, validation, duplicate destinations and submissions, inactive visits, role and office restrictions, repeated scans, sequential enrollee compatibility, Guard adjustments/history, rollback after check-in failure, and migration rollback. JavaScript tests exercise action visibility, form selection/cancellation, assigned/expected office values, checkbox submission, validation errors, loading controls, duplicate submission blocking, and dismissal prevention while saving.

The relevant PHP regression group passed: 25 tests, 219 assertions. All nine frontend tests passed, including five for office adjustments. The full PHP run returned 145 passed, 26 skipped, and one unrelated failure in `ActivityLogFeatureTest::guests_are_redirected_from_activity_logs`: the API returns HTTP 401 while the assertion expects a redirect. The API exception configuration and activity-log routes were not changed.

Manual smoke checks after migration:

1. Scan an active QR at its registered office with hardware and camera; confirm normal check-in and no adjustment actions.
2. At an unregistered office, add with automatic check-in enabled; confirm the success message, a valid check-in in Visit History, preserved original route, and unchanged QR.
3. Repeat with automatic check-in disabled; confirm the visitor appears in Expected Visitors and has no successful check-in until a later scan.
4. At the wrong office, save remarks; confirm the same unauthorized scan has the note, destinations are unchanged, and the unresolved alert remains available to Guards.
5. Try blank/long inputs, duplicate clicks, a different office ID, and an exited/completed visit; confirm rejection and no unintended records.
6. In Guard visitor details, add an office and review history; confirm office staff can subsequently scan the same QR.
7. Check the modal on desktop, tablet, and mobile, especially long routes, keyboard navigation, scrolling, and footer buttons.

Physical scanner/camera devices, visual browser layout, and simultaneous PostgreSQL requests were not exercised by the automated tests. Concurrency protection uses PostgreSQL visit/scan row locks; behavioral tests use isolated SQLite data rather than live visitor records.
